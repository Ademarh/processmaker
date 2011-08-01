CREATE TRIGGER APP_DELEGATION_UPDATE BEFORE UPDATE ON APP_DELEGATION
FOR EACH ROW
BEGIN
  DECLARE DEFAULT_LANG VARCHAR(2);
  DECLARE APP_NUMBER INT;
  DECLARE APP_STATUS VARCHAR(32);
  DECLARE APP_CREATE_DATE DATETIME;
  DECLARE APP_TITLE VARCHAR(255);
  DECLARE APP_PRO_TITLE VARCHAR(255);
  DECLARE APP_TAS_TITLE VARCHAR(255);
  DECLARE APP_CURRENT_USER VARCHAR(255);
  DECLARE PREVIOUS_USR_UID VARCHAR(32);
  DECLARE APP_DEL_PREVIOUS_USER VARCHAR(255);
  DECLARE APP_THREAD_STATUS VARCHAR(32);
  SET @DEFAULT_LANG = '{lang}';
  SELECT APPLICATION.APP_NUMBER into @APP_NUMBER FROM APPLICATION WHERE APP_UID = NEW.APP_UID LIMIT 1;
  SELECT APPLICATION.APP_STATUS into @APP_STATUS FROM APPLICATION WHERE APP_UID = NEW.APP_UID LIMIT 1;
  SELECT APPLICATION.APP_CREATE_DATE into @APP_CREATE_DATE FROM APPLICATION WHERE APP_UID = NEW.APP_UID LIMIT 1;
  SELECT CONTENT.CON_VALUE into @APP_TITLE     FROM CONTENT WHERE NEW.APP_UID=CON_ID AND CON_CATEGORY='APP_TITLE' and CON_LANG = '{lang}' LIMIT 1;
  IF ( @APP_TITLE IS NULL ) THEN
    SET @APP_TITLE = '';
  END IF;
  SELECT CONTENT.CON_VALUE into @APP_PRO_TITLE FROM CONTENT WHERE NEW.PRO_UID=CON_ID AND CON_CATEGORY='PRO_TITLE' and CON_LANG = '{lang}' LIMIT 1;
  SELECT CONTENT.CON_VALUE into @APP_TAS_TITLE FROM CONTENT WHERE NEW.TAS_UID=CON_ID AND CON_CATEGORY='TAS_TITLE' and CON_LANG = '{lang}' LIMIT 1;
  SELECT CONCAT(USERS.USR_LASTNAME,  ' ',  USERS.USR_FIRSTNAME) INTO @APP_CURRENT_USER FROM USERS WHERE USR_UID = NEW.USR_UID LIMIT 1;
  IF ( @APP_CURRENT_USER IS NULL ) THEN
    SET @APP_CURRENT_USER = '';
  END IF;
  IF ( NEW.DEL_PREVIOUS > 0 ) THEN
    SELECT USR_UID INTO @PREVIOUS_USR_UID FROM APP_DELEGATION WHERE APP_UID = NEW.APP_UID AND DEL_INDEX = NEW.DEL_PREVIOUS LIMIT 1;
    SELECT CONCAT(USERS.USR_LASTNAME,  ' ',  USERS.USR_FIRSTNAME) INTO @APP_DEL_PREVIOUS_USER FROM USERS WHERE USR_UID = @PREVIOUS_USR_UID LIMIT 1;
    IF ( @APP_DEL_PREVIOUS_USER IS NULL ) THEN
      SET @APP_DEL_PREVIOUS_USER = '';
    END IF;
  ELSE
    SET @APP_DEL_PREVIOUS_USER = '';
    SET @PREVIOUS_USR_UID = '';
  END IF;
  SELECT APP_THREAD_STATUS INTO @APP_THREAD_STATUS FROM APP_THREAD WHERE APP_UID = NEW.APP_UID AND DEL_INDEX = NEW.DEL_PREVIOUS LIMIT 1;
  IF ( @APP_THREAD_STATUS IS NULL ) THEN
    SET @APP_THREAD_STATUS = 'OPEN';
  END IF;
  SET @TAS_TYPE = (SELECT TAS_TYPE FROM TASK WHERE TAS_UID = NEW.TAS_UID LIMIT 1);
  IF( @TAS_TYPE != 'SUBPROCESS') THEN  
    UPDATE `APP_CACHE_VIEW`
    SET
    APP_NUMBER            =   @APP_NUMBER,
    APP_STATUS            =   @APP_STATUS,
    USR_UID               =   NEW.USR_UID,
    PREVIOUS_USR_UID      =   @PREVIOUS_USR_UID,
    TAS_UID               =   NEW.TAS_UID,
    PRO_UID               =   NEW.PRO_UID,
    DEL_DELEGATE_DATE     =   NEW.DEL_DELEGATE_DATE,
    DEL_INIT_DATE         =   NEW.DEL_INIT_DATE,
    DEL_TASK_DUE_DATE     =   NEW.DEL_TASK_DUE_DATE,
    DEL_FINISH_DATE       =   NEW.DEL_FINISH_DATE,
    DEL_THREAD_STATUS     =   NEW.DEL_THREAD_STATUS,
    APP_THREAD_STATUS     =   @APP_THREAD_STATUS,
    APP_TITLE             =   @APP_TITLE,
    APP_PRO_TITLE         =   @APP_PRO_TITLE,
    APP_TAS_TITLE         =   @APP_TAS_TITLE,
    APP_CURRENT_USER      =   @APP_CURRENT_USER,
    APP_DEL_PREVIOUS_USER =   @APP_DEL_PREVIOUS_USER,
    DEL_PRIORITY          =   NEW.DEL_PRIORITY,
    DEL_DURATION          =   NEW.DEL_DURATION,
    DEL_QUEUE_DURATION    =   NEW.DEL_QUEUE_DURATION,
    DEL_DELAY_DURATION    =   NEW.DEL_DELAY_DURATION,
    DEL_STARTED           =   NEW.DEL_STARTED,
    DEL_FINISHED          =   NEW.DEL_FINISHED,
    DEL_DELAYED           =   NEW.DEL_DELAYED,
    APP_FINISH_DATE       =   NULL,
    APP_UPDATE_DATE       =   NOW(),
    APP_OVERDUE_PERCENTAGE =  NEW.APP_OVERDUE_PERCENTAGE
  WHERE
    APP_UID = NEW.APP_UID
    AND DEL_INDEX =   NEW.DEL_INDEX;
  END IF;
END
