<?php
  //get the action from GET or POST, default is todo
  $action   = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : 'todo');
  //fix a previous inconsistency
  if ( $action == 'selfservice' ) $action = 'unassigned';


  G::LoadClass ( "BasePeer" );
  G::LoadClass ( 'configuration' );
  require_once ( "classes/model/Fields.php" );
  require_once ( "classes/model/AppCacheView.php" );
  require_once ( "classes/model/Process.php" );
  require_once ( "classes/model/Users.php" );

  $oHeadPublisher   =& headPublisher::getSingleton();
  // oHeadPublisher->setExtSkin( 'xtheme-blue');

  //get the configuration for this action
  $conf = new Configurations();
  try {
  	// the setup for search is the same as the Sent (participated)
    $confCasesList = $conf->getConfiguration('casesList', $action=='search' ? 'search': $action );
  } 
  catch (Exception $e){
    $confCasesList = array();
  }

  // reassign header configuration
  $confReassignList = getReassignList();

  // evaluates an action and the list that will be rendered
  $config       = getAdditionalFields($action, $confCasesList);
  $columns      = $config['caseColumns'];
  $readerFields = $config['caseReaderFields'];
  $reassignColumns      = $confReassignList['caseColumns'];
  $reassignReaderFields = $confReassignList['caseReaderFields'];

  if ( $action == 'draft' /* &&  $action == 'cancelled' */) {
    //array_unshift ( $columns, array( 'header'=> '', 'width'=> 50, 'sortable'=> false, 'id'=> 'deleteLink' ) );
  }
  if ( $action == 'selfservice' ) {
    array_unshift ( $columns, array( 'header'=> '', 'width'=> 50, 'sortable'=> false, 'id'=> 'viewLink' ) );
  }

  if ( $action == 'paused' ) {
    array_unshift ( $columns, array( 'header'=> '', 'width'=> 50, 'sortable'=> false, 'id'=> 'unpauseLink' ) );
  }
/*
  if ( $action == 'to_reassign' ) {
    array_unshift ( $columns, array( 'header'=> '', 'width'=> 50, 'sortable'=> false, 'id'=> 'reassignLink' ) );
  }
*/
//  if ( $action == 'cancelled' ) {
//    array_unshift ( $columns, array( 'header'=> '', 'width'=> 50, 'sortable'=> false, 'id'=> 'reactivateLink' ) );
//  }

  $userUid = ( isset($_SESSION['USER_LOGGED'] ) && $_SESSION['USER_LOGGED'] != '' ) ? $_SESSION['USER_LOGGED'] : null;
  $oAppCache = new AppCacheView();
  $oAppCache->confCasesList = $confCasesList;
  
  //get values for the comboBoxes
  $processes = getProcessArray($action, $userUid );
  $status    = getStatusArray($action, $userUid );
  $users     = getUserArray($action, $userUid );
  $allUsers  = getAllUsersArray($action);

  $oHeadPublisher->assign( 'reassignReaderFields',  $reassignReaderFields );  //sending the fields to get from proxy
  $oHeadPublisher->addExtJsScript('cases/reassignList', true );
  $oHeadPublisher->assign( 'pageSize',      intval($config['rowsperpage']) ); //sending the page size
  $oHeadPublisher->assign( 'columns',       $columns );                       //sending the columns to display in grid
  $oHeadPublisher->assign( 'readerFields',  $readerFields );                  //sending the fields to get from proxy
  $oHeadPublisher->assign( 'reassignColumns',       $reassignColumns );       //sending the columns to display in grid
  $oHeadPublisher->assign( 'action',        $action );                        //sending the fields to get from proxy
  $oHeadPublisher->assign( 'PMDateFormat',  $config['dateformat'] );          //sending the fields to get from proxy
  $oHeadPublisher->assign( 'statusValues',  $status );                        //sending the columns to display in grid
  $oHeadPublisher->assign( 'processValues', $processes);                      //sending the columns to display in grid
  $oHeadPublisher->assign( 'userValues',    $users);                          //sending the columns to display in grid
  $oHeadPublisher->assign( 'allUsersValues',$allUsers);                       //sending the columns to display in grid
  
  
  $TRANSLATIONS->LABEL_GRID_LOADING          = G::LoadTranslation('ID_CASES_LIST_GRID_LOADING');
  $TRANSLATIONS->LABEL_REFRESH               = G::LoadTranslation('ID_REFRESH_LABEL');
  $TRANSLATIONS->MESSAGE_REFRESH             = G::LoadTranslation('ID_REFRESH_MESSAGE');
  $TRANSLATIONS->LABEL_OPT_READ              = G::LoadTranslation('ID_OPT_READ');
  $TRANSLATIONS->LABEL_OPT_UNREAD            = G::LoadTranslation('ID_OPT_UNREAD');
  $TRANSLATIONS->LABEL_OPT_ALL               = G::LoadTranslation('ID_OPT_ALL');
  $TRANSLATIONS->LABEL_OPT_STARTED           = G::LoadTranslation('ID_OPT_STARTED');
  $TRANSLATIONS->LABEL_OPT_COMPLETED         = G::LoadTranslation('ID_OPT_COMPLETED');
  $TRANSLATIONS->LABEL_EMPTY_PROCESSES       = G::LoadTranslation('ID_EMPTY_PROCESSES');
  $TRANSLATIONS->LABEL_EMPTY_USERS           = G::LoadTranslation('ID_EMPTY_USERS');
  $TRANSLATIONS->LABEL_EMPTY_SEARCH          = G::LoadTranslation('ID_EMPTY_SEARCH');
  $TRANSLATIONS->LABEL_EMPTY_CASE            = G::LoadTranslation('ID_EMPTY_CASE');
  $TRANSLATIONS->LABEL_SEARCH                = G::LoadTranslation('ID_SEARCH');
  $TRANSLATIONS->LABEL_OPT_JUMP              = G::LoadTranslation('ID_OPT_JUMP');
  $TRANSLATIONS->LABEL_DISPLAY_ITEMS         = G::LoadTranslation('ID_DISPLAY_ITEMS');
  $TRANSLATIONS->LABEL_DISPLAY_EMPTY         = G::LoadTranslation('ID_DISPLAY_EMPTY');
  $TRANSLATIONS->LABEL_OPEN_CASE             = G::LoadTranslation('ID_OPEN_CASE');
  
  $TRANSLATIONS->ID_CASESLIST_APP_UID        = G::LoadTranslation('ID_CASESLIST_APP_UID');
  $TRANSLATIONS->ID_CONFIRM                  = G::LoadTranslation('ID_CONFIRM');
  $TRANSLATIONS->ID_MSG_CONFIRM_DELETE_CASES = G::LoadTranslation('ID_MSG_CONFIRM_DELETE_CASES');
  $TRANSLATIONS->ID_DELETE                   = G::LoadTranslation('ID_DELETE');
  $TRANSLATIONS->ID_REASSIGN                 = G::LoadTranslation('ID_REASSIGN');
  $TRANSLATIONS->ID_VIEW                     = G::LoadTranslation('ID_VIEW');
  $TRANSLATIONS->ID_UNPAUSE                  = G::LoadTranslation('ID_UNPAUSE'); 		 			
  $TRANSLATIONS->ID_PROCESSING               = G::LoadTranslation('ID_PROCESSING'); 		 			
  $TRANSLATIONS->ID_CONFIRM_UNPAUSE_CASE     = G::LoadTranslation('ID_CONFIRM_UNPAUSE_CASE'); 		 			
  
  $TRANSLATIONS->ID_PROCESS                  = G::LoadTranslation('ID_PROCESS');
  $TRANSLATIONS->ID_STATUS                   = G::LoadTranslation('ID_STATUS');
  $TRANSLATIONS->ID_USER                     = G::LoadTranslation('ID_USER');
  $TRANSLATIONS->ID_DELEGATE_DATE_FROM       = G::LoadTranslation('ID_DELEGATE_DATE_FROM');
  $TRANSLATIONS->ID_TO                       = G::LoadTranslation('ID_TO');
  $TRANSLATIONS->ID_FILTER_BY_DELEGATED_DATE = G::LoadTranslation('ID_FILTER_BY_DELEGATED_DATE');

  $TRANSLATIONS->ID_DELEGATE_DATE_TO = G::LoadTranslation('ID_TO_DO');
  $TRANSLATIONS->ID_DELEGATE_DATE_TO = G::LoadTranslation('ID_DRAFT');
  $TRANSLATIONS->ID_DELEGATE_DATE_TO = G::LoadTranslation('ID_COMPLETED');
  $TRANSLATIONS->ID_DELEGATE_DATE_TO = G::LoadTranslation('ID_CANCELLED');
  $TRANSLATIONS->ID_DELEGATE_DATE_TO = G::LoadTranslation('ID_PAUSED');


  $oHeadPublisher->assign( 'TRANSLATIONS',   $TRANSLATIONS); //translations
  
  $oHeadPublisher->addExtJsScript('cases/casesList', true );    //adding a javascript file .js

  $oHeadPublisher->addContent( 'cases/casesListExtJs'); //adding a html file  .html.

  G::RenderPage('publish', 'extJs');
 
  //functions to fill the comboboxes in the case list page 
  function getProcessArray ( $action, $userUid ) {
  	global $oAppCache;
    $processes = Array();
    $processes[] = array ( '', G::LoadTranslation('ID_ALL_PROCESS') );
  
//get the list based in the action provided
    switch ( $action ) {
      case 'draft' :
           $cProcess      = $oAppCache->getDraftListCriteria($userUid); //fast enough
           break;
      case 'sent' :
           $cProcess      = $oAppCache->getSentListProcessCriteria ($userUid); // fast enough
           break;
      case 'search' :
           //in search action, the query to obtain all process is too slow, so we need to query directly to 
           //process and content tables, and for that reason we need the current language in AppCacheView.
           G::loadClass('configuration');
           $oConf = new Configurations; 
           $oConf->loadConfig($x, 'APP_CACHE_VIEW_ENGINE','','','','');
           $appCacheViewEngine = $oConf->aConfig;
           $lang   = isset($appCacheViewEngine['LANG']) ? $appCacheViewEngine['LANG'] : 'en';
           
           $cProcess      = new Criteria('workflow');
           $cProcess->clearSelectColumns ( );
           $cProcess->addSelectColumn ( ProcessPeer::PRO_UID );
           $cProcess->addSelectColumn ( ContentPeer::CON_VALUE );
           $del = DBAdapter::getStringDelimiter();
           $conds = array();
           $conds[] = array(ProcessPeer::PRO_UID,      ContentPeer::CON_ID );
           $conds[] = array(ContentPeer::CON_CATEGORY, $del . 'PRO_TITLE' . $del);
           $conds[] = array(ContentPeer::CON_LANG,     $del . $lang . $del);
           $cProcess->addJoinMC($conds, Criteria::LEFT_JOIN);           
           $cProcess->add(ProcessPeer::PRO_STATUS, 'ACTIVE');
           $oDataset = ProcessPeer::doSelectRS($cProcess);
           $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
           $oDataset->next();
               
           while($aRow = $oDataset->getRow()){
             $processes[] = array ( $aRow['PRO_UID'], $aRow['CON_VALUE'] );
             $oDataset->next();
           }
           
           return $processes;  
           break;
      case 'selfservice' :
           $cProcess      = $oAppCache->getUnassignedListCriteria($userUid);
           break;
      case 'paused' :
           $cProcess      = $oAppCache->getPausedListCriteria($userUid);
           break;
      case 'to_revise' :
           $cProcess      = $oAppCache->getToReviseListCriteria($userUid);
           break;
      case 'to_reassign' :
           $cProcess      = $oAppCache->getToReassignListCriteria();
           $cProcess->addDescendingOrderByColumn(AppCacheViewPeer::APP_PRO_TITLE);
//             return $processes;
           
//           $params = array();
//           $sqlString = BasePeer::createSelectSql($cProcess, $params);
//           var_dump ($sqlString);

           break;
      case 'todo' :
      default:
           $cProcess      = $oAppCache->getToDoListCriteria($userUid); //fast enough
      break;
    }
  
    //get the processes for this user in this action
    $cProcess->clearSelectColumns ( );
    $cProcess->setDistinct();
    $cProcess->addSelectColumn ( AppCacheViewPeer::PRO_UID );
    $cProcess->addSelectColumn ( AppCacheViewPeer::APP_PRO_TITLE );
    $oDataset = AppCacheViewPeer::doSelectRS($cProcess);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
        
    while($aRow = $oDataset->getRow()){
      $processes[] = array ( $aRow['PRO_UID'], $aRow['APP_PRO_TITLE'] );
      $oDataset->next();
    }
    
    return $processes;  
  }

  function getUserArray ( $action, $userUid ) {
  	global $oAppCache;
    $status = array();
    $users[] = array( '', G::LoadTranslation('ID_ALL_USERS') );
    //now get users, just for the Search action
    switch ( $action ) {
      case 'search' :
           $cUsers = new Criteria('workflow');
           $cUsers->clearSelectColumns ( );
           $cUsers->addSelectColumn ( UsersPeer::USR_UID );
           $cUsers->addSelectColumn ( UsersPeer::USR_FIRSTNAME );
           $cUsers->addSelectColumn ( UsersPeer::USR_LASTNAME );
           $oDataset = UsersPeer::doSelectRS($cUsers);
           $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
           $oDataset->next();
           while($aRow = $oDataset->getRow()){
             $users[] = array( $aRow['USR_UID'], $aRow['USR_LASTNAME'] . ' ' . $aRow['USR_FIRSTNAME'] );
             $oDataset->next();
           }
           break;
      default:
           return $users;
      break;
    }
    return $users;
  }

  function getAllUsersArray ( $action ) {
    global $oAppCache;
    $status = array();
    $users[] = array( '', G::LoadTranslation('ID_ALL_USERS') );
    if ($action=='to_reassign') {
      //now get users, just for the Search action
      $cUsers = new Criteria('workflow');
      $cUsers->clearSelectColumns ( );
      $cUsers->addSelectColumn ( AppCacheViewPeer::USR_UID );
      $cUsers->setDistinct();
      $cUsers->addSelectColumn ( AppCacheViewPeer::APP_CURRENT_USER );
      $cUsers->addAscendingOrderByColumn ( AppCacheViewPeer::APP_CURRENT_USER );
      $oDataset = AppCacheViewPeer::doSelectRS($cUsers);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      while($aRow = $oDataset->getRow()){
        $users[] = array( $aRow['USR_UID'], $aRow['APP_CURRENT_USER'] );
        $oDataset->next();
      }
    }
    return $users;
  }

  function getStatusArray ( $action, $userUid ) {
  	global $oAppCache;
    $status = array();
    $status[] = array( '',  G::LoadTranslation('ID_ALL_STATUS') );
//get the list based in the action provided
    switch ( $action ) {
      case 'sent' :
           $cStatus       = $oAppCache->getSentListProcessCriteria ($userUid); // a little slow
           break;
      case 'search' :
           $cStatus = new Criteria('workflow');
           $cStatus->clearSelectColumns ( );
           $cStatus->setDistinct();
           $cStatus->addSelectColumn ( ApplicationPeer::APP_STATUS );
           $oDataset = ApplicationPeer::doSelectRS($cStatus);
           $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
           $oDataset->next();
           while($aRow = $oDataset->getRow()){
             $status[] = array( $aRow['APP_STATUS'], G::LoadTranslation('ID_CASES_STATUS_'.$aRow['APP_STATUS'])  ); //here we can have a translation for the status ( the second param)
             $oDataset->next();
           }
           return $status;
           break;
           
      case 'selfservice' :
           $cStatus       = $oAppCache->getUnassignedListCriteria($userUid);
           break;
      case 'paused' :
           $cStatus       = $oAppCache->getPausedListCriteria($userUid);
           break;
      case 'to_revise' :
           $cStatus       = $oAppCache->getToReviseListCriteria($userUid);
//           $cStatus       = $oAppCache->getPausedListCriteria($userUid);
           break;
      case 'to_reassign' :
           $cStatus       = $oAppCache->getToReassignListCriteria();
           break;
      case 'todo' :
      case 'draft' :
//      case 'to_revise' :
      default:
           return $status;
      break;
    }

    //get the status for this user in this action only for participated, unassigned, paused
//    if ( $action != 'todo' && $action != 'draft' && $action != 'to_revise') {
    if ( $action != 'todo' && $action != 'draft' ) {
      //$cStatus = new Criteria('workflow');
      $cStatus->clearSelectColumns ( );
      $cStatus->setDistinct();
      $cStatus->addSelectColumn ( AppCacheViewPeer::APP_STATUS );
      $oDataset = AppCacheViewPeer::doSelectRS($cStatus);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      while($aRow = $oDataset->getRow()){
        $status[] = array( $aRow['APP_STATUS'], G::LoadTranslation('ID_CASES_STATUS_'.$aRow['APP_STATUS'])  ); //here we can have a translation for the status ( the second param)
        $oDataset->next();
      }
    }
    return $status;
  }
  
  //these getXX function gets the default fields in casesListSetup
  function getToDo() {
    $caseColumns = array ();
    $caseColumns[] = array( 'header' =>'#',           'dataIndex' => 'APP_NUMBER',          'width' => 45, 'align' => 'center');
    $caseColumns[] = array( 'header' =>'Case',        'dataIndex' => 'APP_TITLE',           'width' => 150 );
    $caseColumns[] = array( 'header' =>'Task',        'dataIndex' => 'APP_TAS_TITLE',       'width' => 120 );
    $caseColumns[] = array( 'header' =>'Process',     'dataIndex' => 'APP_PRO_TITLE',       'width' => 120 );
    $caseColumns[] = array( 'header' =>'Sent by',     'dataIndex' => 'APP_DEL_PREVIOUS_USER', 'width' => 90 );
    $caseColumns[] = array( 'header' =>'Due Date',    'dataIndex' => 'DEL_TASK_DUE_DATE',   'width' => 110);
    $caseColumns[] = array( 'header' =>'Last Modify', 'dataIndex' => 'APP_UPDATE_DATE',     'width' => 110 );
    $caseColumns[] = array( 'header' =>'Priority',    'dataIndex' => 'DEL_PRIORITY',        'width' => 50 );
    
    $caseReaderFields = array();
    $caseReaderFields[] = array( 'name' => 'APP_UID' );
    $caseReaderFields[] = array( 'name' => 'DEL_INDEX' );
    $caseReaderFields[] = array( 'name' => 'APP_NUMBER' );
    $caseReaderFields[] = array( 'name' => 'APP_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_PRO_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_TAS_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_DEL_PREVIOUS_USER' );
    $caseReaderFields[] = array( 'name' => 'DEL_TASK_DUE_DATE' );
    $caseReaderFields[] = array( 'name' => 'APP_UPDATE_DATE' );
    $caseReaderFields[] = array( 'name' => 'DEL_PRIORITY' );
    $caseReaderFields[] = array( 'name' => 'APP_FINISH_DATE' );

    $caseReaderFields[] = array( 'name' => 'APP_CURRENT_USER' );
    $caseReaderFields[] = array( 'name' => 'APP_STATUS' );
    
    return array ( 'caseColumns' => $caseColumns, 'caseReaderFields' => $caseReaderFields, 'rowsperpage' => 20, 'dateformat' => 'M d, Y' );  
  }
  
  function getDraft() {
    $caseColumns = array ();
    $caseColumns[] = array( 'header' =>'#',           'dataIndex' => 'APP_NUMBER',        'width' => 45, 'align' => 'center');
    $caseColumns[] = array( 'header' =>'Case',        'dataIndex' => 'APP_TITLE',         'width' => 150 );
    $caseColumns[] = array( 'header' =>'Task',        'dataIndex' => 'APP_TAS_TITLE',     'width' => 120 );
    $caseColumns[] = array( 'header' =>'Process',     'dataIndex' => 'APP_PRO_TITLE',     'width' => 120 );
    $caseColumns[] = array( 'header' =>'Due Date',    'dataIndex' => 'DEL_TASK_DUE_DATE', 'width' => 110);
    $caseColumns[] = array( 'header' =>'Last Modify', 'dataIndex' => 'APP_UPDATE_DATE',   'width' => 110 );
    $caseColumns[] = array( 'header' =>'Priority',    'dataIndex' => 'DEL_PRIORITY',      'width' => 50 );
    
    $caseReaderFields = array();
    $caseReaderFields[] = array( 'name' => 'APP_UID' );
    $caseReaderFields[] = array( 'name' => 'APP_NUMBER' );
    $caseReaderFields[] = array( 'name' => 'APP_STATUS' );
    $caseReaderFields[] = array( 'name' => 'DEL_INDEX' );
    $caseReaderFields[] = array( 'name' => 'APP_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_PRO_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_TAS_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_DEL_PREVIOUS_USER' );
    $caseReaderFields[] = array( 'name' => 'APP_CURRENT_USER' );
    $caseReaderFields[] = array( 'name' => 'DEL_TASK_DUE_DATE' );
    $caseReaderFields[] = array( 'name' => 'APP_UPDATE_DATE' );
    $caseReaderFields[] = array( 'name' => 'DEL_PRIORITY' );
    $caseReaderFields[] = array( 'name' => 'APP_STATUS' );
    $caseReaderFields[] = array( 'name' => 'APP_FINISH_DATE' );

    return array ( 'caseColumns' => $caseColumns, 'caseReaderFields' => $caseReaderFields, 'rowsperpage' => 20, 'dateformat' => 'M d, Y'  );
  }

  function getParticipated() {
    $caseColumns = array ();
    $caseColumns[] = array( 'header' =>'#',            'dataIndex' => 'APP_NUMBER',        'width' => 45, 'align' => 'center');
    $caseColumns[] = array( 'header' =>'Case',         'dataIndex' => 'APP_TITLE',         'width' => 150 );
    $caseColumns[] = array( 'header' =>'Task',         'dataIndex' => 'APP_TAS_TITLE',     'width' => 120 );
    $caseColumns[] = array( 'header' =>'Process',      'dataIndex' => 'APP_PRO_TITLE',     'width' => 120 );
    $caseColumns[] = array( 'header' =>'sent By',      'dataIndex' => 'APP_DEL_PREVIOUS_USER', 'width' => 120 );
    $caseColumns[] = array( 'header' =>'Current User', 'dataIndex' => 'APP_CURRENT_USER', 'width' => 120 );
    $caseColumns[] = array( 'header' =>'Last Modify',  'dataIndex' => 'APP_UPDATE_DATE',  'width' => 80 );
    $caseColumns[] = array( 'header' =>'Status',       'dataIndex' => 'APP_STATUS',       'width' => 50 );
    
    $caseReaderFields = array();
    $caseReaderFields[] = array( 'name' => 'APP_UID' );
    $caseReaderFields[] = array( 'name' => 'APP_NUMBER' );
    $caseReaderFields[] = array( 'name' => 'APP_STATUS' );
    $caseReaderFields[] = array( 'name' => 'DEL_INDEX' );
    $caseReaderFields[] = array( 'name' => 'APP_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_PRO_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_TAS_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_DEL_PREVIOUS_USER' );
    $caseReaderFields[] = array( 'name' => 'APP_CURRENT_USER' );
    $caseReaderFields[] = array( 'name' => 'DEL_TASK_DUE_DATE' );
    $caseReaderFields[] = array( 'name' => 'APP_UPDATE_DATE' );
    $caseReaderFields[] = array( 'name' => 'DEL_PRIORITY' );
    $caseReaderFields[] = array( 'name' => 'APP_STATUS' );
    $caseReaderFields[] = array( 'name' => 'APP_FINISH_DATE' );

    return array ( 'caseColumns' => $caseColumns, 'caseReaderFields' => $caseReaderFields, 'rowsperpage' => 20, 'dateformat' => 'M d, Y'  );
   }

   function getSearch() {
    $caseColumns = array ();
    $caseColumns[] = array( 'header' =>'#',            'dataIndex' => 'APP_NUMBER',        'width' => 45, 'align' => 'center');
    $caseColumns[] = array( 'header' =>'Case',         'dataIndex' => 'APP_TITLE',         'width' => 100 );
    $caseColumns[] = array( 'header' =>'Task',         'dataIndex' => 'APP_TAS_TITLE',     'width' => 120 );
    $caseColumns[] = array( 'header' =>'Process',      'dataIndex' => 'APP_PRO_TITLE',     'width' => 120 );
    $caseColumns[] = array( 'header' =>'sent By',      'dataIndex' => 'APP_DEL_PREVIOUS_USER', 'width' => 120 );
    $caseColumns[] = array( 'header' =>'Current User', 'dataIndex' => 'APP_CURRENT_USER', 'width' => 120 );
    $caseColumns[] = array( 'header' =>'Last Modify',     'dataIndex' => 'APP_UPDATE_DATE',  'width' => 80 );
    $caseColumns[] = array( 'header' =>'Delegation Date', 'dataIndex' => 'DEL_DELEGATE_DATE','width' => 80 );
    $caseColumns[] = array( 'header' =>'Status',       'dataIndex' => 'APP_STATUS',      'width' => 50 );

    $caseReaderFields = array();
    $caseReaderFields[] = array( 'name' => 'APP_UID' );
    $caseReaderFields[] = array( 'name' => 'APP_NUMBER' );
    $caseReaderFields[] = array( 'name' => 'APP_STATUS' );
    $caseReaderFields[] = array( 'name' => 'DEL_INDEX' );
    $caseReaderFields[] = array( 'name' => 'APP_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_PRO_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_TAS_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_DEL_PREVIOUS_USER' );
    $caseReaderFields[] = array( 'name' => 'APP_CURRENT_USER' );
    $caseReaderFields[] = array( 'name' => 'DEL_TASK_DUE_DATE' );
    $caseReaderFields[] = array( 'name' => 'APP_UPDATE_DATE' );
    $caseReaderFields[] = array( 'name' => 'DEL_DELEGATE_DATE' );
    $caseReaderFields[] = array( 'name' => 'DEL_PRIORITY' );
    $caseReaderFields[] = array( 'name' => 'APP_STATUS' );
    $caseReaderFields[] = array( 'name' => 'APP_FINISH_DATE' );

    return array ( 'caseColumns' => $caseColumns, 'caseReaderFields' => $caseReaderFields, 'rowsperpage' => 20, 'dateformat' => 'M d, Y'  );
   }
  
  function getUnassigned() {
    $caseColumns = array ();
    $caseColumns[] = array( 'header' =>'#',           'dataIndex' => 'APP_NUMBER',        'width' => 45, 'align' => 'center');
    $caseColumns[] = array( 'header' =>'Case',        'dataIndex' => 'APP_TITLE',         'width' => 150 );
    $caseColumns[] = array( 'header' =>'Task',        'dataIndex' => 'APP_TAS_TITLE',     'width' => 120 );
    $caseColumns[] = array( 'header' =>'Process',     'dataIndex' => 'APP_PRO_TITLE',     'width' => 120 );
    $caseColumns[] = array( 'header' =>'Completed By User', 'dataIndex' => 'APP_CURRENT_USER',   'width' => 110 );
    $caseColumns[] = array( 'header' =>'Finish Date',    'dataIndex' => 'APP_FINISH_DATE',      'width' => 50 );

    $caseReaderFields = array();
    $caseReaderFields[] = array( 'name' => 'APP_UID' );
    $caseReaderFields[] = array( 'name' => 'APP_NUMBER' );
    $caseReaderFields[] = array( 'name' => 'APP_STATUS' );
    $caseReaderFields[] = array( 'name' => 'DEL_INDEX' );
    $caseReaderFields[] = array( 'name' => 'APP_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_PRO_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_TAS_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_DEL_PREVIOUS_USER' );
    $caseReaderFields[] = array( 'name' => 'APP_CURRENT_USER' );
    $caseReaderFields[] = array( 'name' => 'DEL_TASK_DUE_DATE' );
    $caseReaderFields[] = array( 'name' => 'APP_UPDATE_DATE' );
    $caseReaderFields[] = array( 'name' => 'DEL_PRIORITY' );
    $caseReaderFields[] = array( 'name' => 'APP_STATUS' );
    $caseReaderFields[] = array( 'name' => 'APP_FINISH_DATE' );

    return array ( 'caseColumns' => $caseColumns, 'caseReaderFields' => $caseReaderFields, 'rowsperpage' => 20, 'dateformat' => 'M d, Y'  );
  }

  function getPaused() {
    $caseColumns = array ();
    $caseColumns[] = array( 'header' =>'#',           'dataIndex' => 'APP_NUMBER',        'width' => 45, 'align' => 'center');
    $caseColumns[] = array( 'header' =>'Case',        'dataIndex' => 'APP_TITLE',         'width' => 150 );
    $caseColumns[] = array( 'header' =>'Task',        'dataIndex' => 'APP_TAS_TITLE',     'width' => 120 );
    $caseColumns[] = array( 'header' =>'Process',     'dataIndex' => 'APP_PRO_TITLE',     'width' => 120 );
    $caseColumns[] = array( 'header' =>'Sent By',     'dataIndex' => 'APP_DEL_PREVIOUS_USER', 'width' => 90 );

    $caseReaderFields = array();
    $caseReaderFields[] = array( 'name' => 'APP_UID' );
    $caseReaderFields[] = array( 'name' => 'APP_NUMBER' );
    $caseReaderFields[] = array( 'name' => 'APP_STATUS' );
    $caseReaderFields[] = array( 'name' => 'DEL_INDEX' );
    $caseReaderFields[] = array( 'name' => 'APP_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_PRO_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_TAS_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_DEL_PREVIOUS_USER' );
    $caseReaderFields[] = array( 'name' => 'APP_CURRENT_USER' );
    $caseReaderFields[] = array( 'name' => 'DEL_TASK_DUE_DATE' );
    $caseReaderFields[] = array( 'name' => 'APP_UPDATE_DATE' );
    $caseReaderFields[] = array( 'name' => 'DEL_PRIORITY' );
    $caseReaderFields[] = array( 'name' => 'APP_STATUS' );
    $caseReaderFields[] = array( 'name' => 'APP_FINISH_DATE' );

    return array ( 'caseColumns' => $caseColumns, 'caseReaderFields' => $caseReaderFields, 'rowsperpage' => 20, 'dateformat' => 'M d, Y'  );
  }

  function getToRevise() {
    $caseColumns = array ();
    $caseColumns[] = array( 'header' =>'#',            'dataIndex' => 'APP_NUMBER',        'width' => 45, 'align' => 'center');
    $caseColumns[] = array( 'header' =>'Case',         'dataIndex' => 'APP_TITLE',         'width' => 150 );
    $caseColumns[] = array( 'header' =>'Task',         'dataIndex' => 'APP_TAS_TITLE',     'width' => 120 );
    $caseColumns[] = array( 'header' =>'Process',      'dataIndex' => 'APP_PRO_TITLE',     'width' => 120 );
    $caseColumns[] = array( 'header' =>'Current User', 'dataIndex' => 'APP_CURRENT_USER',  'width' => 90 );
    $caseColumns[] = array( 'header' =>'Sent By',      'dataIndex' => 'APP_DEL_PREVIOUS_USER', 'width' => 90 );
    $caseColumns[] = array( 'header' =>'Last Modify',  'dataIndex' => 'APP_UPDATE_DATE',   'width' => 110 );
    $caseColumns[] = array( 'header' =>'Priority',     'dataIndex' => 'DEL_PRIORITY',      'width' => 50 );
    $caseColumns[] = array( 'header' =>'Status',       'dataIndex' => 'APP_STATUS',        'width' => 50 );

    $caseReaderFields = array();
    $caseReaderFields[] = array( 'name' => 'APP_UID' );
    $caseReaderFields[] = array( 'name' => 'APP_NUMBER' );
    $caseReaderFields[] = array( 'name' => 'DEL_INDEX' );
    $caseReaderFields[] = array( 'name' => 'APP_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_PRO_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_TAS_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_DEL_PREVIOUS_USER' );
    $caseReaderFields[] = array( 'name' => 'APP_CURRENT_USER' );
    $caseReaderFields[] = array( 'name' => 'DEL_TASK_DUE_DATE' );
    $caseReaderFields[] = array( 'name' => 'APP_UPDATE_DATE' );
    $caseReaderFields[] = array( 'name' => 'DEL_PRIORITY' );
    $caseReaderFields[] = array( 'name' => 'APP_STATUS' );
    $caseReaderFields[] = array( 'name' => 'APP_FINISH_DATE' );

    return array ( 'caseColumns' => $caseColumns, 'caseReaderFields' => $caseReaderFields, 'rowsperpage' => 20, 'dateformat' => 'M d, Y'  );
  }

  function getToReassign() {
    $caseColumns = array ();
    $caseColumns[] = array( 'header' =>'#',            'dataIndex' => 'APP_NUMBER',        'width' => 45, 'align' => 'center');
    $caseColumns[] = array( 'header' =>'Case',         'dataIndex' => 'APP_TITLE',         'width' => 150 );
    $caseColumns[] = array( 'header' =>'Task',         'dataIndex' => 'APP_TAS_TITLE',     'width' => 120 );
    $caseColumns[] = array( 'header' =>'Process',      'dataIndex' => 'APP_PRO_TITLE',     'width' => 120 );
    $caseColumns[] = array( 'header' =>'Current User', 'dataIndex' => 'APP_CURRENT_USER',  'width' => 90 );
    $caseColumns[] = array( 'header' =>'Sent By',      'dataIndex' => 'APP_DEL_PREVIOUS_USER', 'width' => 90 );
    $caseColumns[] = array( 'header' =>'Last Modify',  'dataIndex' => 'APP_UPDATE_DATE',   'width' => 110 );
    $caseColumns[] = array( 'header' =>'Status',       'dataIndex' => 'APP_STATUS',        'width' => 50 );

    $caseReaderFields = array();
    $caseReaderFields[] = array( 'name' => 'APP_UID' );
    $caseReaderFields[] = array( 'name' => 'APP_NUMBER' );
    $caseReaderFields[] = array( 'name' => 'APP_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_TAS_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_PRO_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_CURRENT_USER' );
    $caseReaderFields[] = array( 'name' => 'APP_DEL_PREVIOUS_USER' );
    $caseReaderFields[] = array( 'name' => 'APP_UPDATE_DATE' );
    $caseReaderFields[] = array( 'name' => 'APP_STATUS' );

    return array ( 'caseColumns' => $caseColumns, 'caseReaderFields' => $caseReaderFields, 'rowsperpage' => 20, 'dateformat' => 'M d, Y'  );
  }

  /**
   * get the list configuration headers of the cases checked for reassign, for the
   * reassign cases list.
   */
  function getReassignList() {
    $caseColumns = array ();
    $caseColumns[] = array( 'header' =>'Case',         'dataIndex' => 'APP_TITLE',         'width' => 100 );
    $caseColumns[] = array( 'header' =>'CaseId',       'dataIndex' => 'APP_UID'  ,         'width' => 200 );
    $caseColumns[] = array( 'header' =>'User',         'dataIndex' => 'USR_UID'  ,         'width' => 200 );
    $caseColumns[] = array( 'header' =>'Task',         'dataIndex' => 'APP_TAS_TITLE',     'width' => 120 );
    $caseColumns[] = array( 'header' =>'Process',      'dataIndex' => 'APP_PRO_TITLE',     'width' => 120 );
//    $caseColumns[] = array( 'header' =>'Status',       'dataIndex' => 'APP_STATUS',        'width' => 50 );
    $caseColumns[] = array( 'header' =>'Reassign To',  'dataIndex' => 'USERS',             'width' => 120 , 'editor'=>"combo", 'renderer'=>"Ext.util.Format.comboRenderer(combo)");

    $caseReaderFields = array();
    $caseReaderFields[] = array( 'name' => 'APP_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_UID' );
    $caseReaderFields[] = array( 'name' => 'USR_UID' );
    $caseReaderFields[] = array( 'name' => 'APP_TAS_TITLE' );
    $caseReaderFields[] = array( 'name' => 'APP_PRO_TITLE' );
//    $caseReaderFields[] = array( 'name' => 'APP_STATUS' );
//    $caseReaderFields[] = array( 'name' => 'APP_STATUS' );
//    $caseReaderFields[] = array( 'name' => 'USERS' );

    return array ( 'caseColumns' => $caseColumns, 'caseReaderFields' => $caseReaderFields, 'rowsperpage' => 20, 'dateformat' => 'M d, Y'  );
  }

  function getReassignUsersList() {
    $caseColumns = array ();

    $caseReaderFields = array();
    $caseReaderFields[] = array( 'name' => 'userUid' );
    $caseReaderFields[] = array( 'name' => 'userFullname' );

    return array ( 'caseColumns' => $caseColumns, 'caseReaderFields' => $caseReaderFields, 'rowsperpage' => 20, 'dateformat' => 'M d, Y'  );
  }

//  Ext.namespace("Ext.ux");
//  Ext.ux.comboBoxRenderer = function(combo) {
//    return function(value) {
//      var idx = combo.store.find(combo.valueField, value);
//      var rec = combo.store.getAt(idx);
//      return rec.get(combo.displayField);
//    }
//  }
  /**
   * loads the PM Table field list from the database based in an action parameter
   * then assemble the List of fields with these data, for the configuration in cases list.
   * @param  String $action
   * @return Array $config
   */
function getAdditionalFields($action, $confCasesList){
  $caseColumns = array();
  $caseReaderFields = array();

  if ( !empty($confCasesList) && !empty($confCasesList['second']['data'])) {
    foreach($confCasesList['second']['data'] as $fieldData){
      if ( $fieldData['fieldType']!='key' ) {
//        $label = ($fieldData['fieldType']=='case field' ) ? G::loadTranslation('ID_CASESLIST_'.$fieldData['name']) : $fieldData['label'];
        $label = $fieldData['label'];
        $caseColumns[]      = array( 'header' => $label, 'dataIndex' => $fieldData['name'], 'width' => $fieldData['width'], 'align' => $fieldData['align'] );
        $caseReaderFields[] = array( 'name'   => $fieldData['name'] );
      }
    }
    return array ( 'caseColumns' => $caseColumns, 'caseReaderFields' => $caseReaderFields, 'rowsperpage' => $confCasesList['rowsperpage'], 'dateformat' => $confCasesList['dateformat'] );
  } 
  else {  //seems this is only in case this user dont have the configuration for this action.
    switch ( $action ) {
      case 'draft' :
        $config = getDraft();
        break;
      case 'search' :
        $config = getSearch();
        break;
      case 'participated' :
        $config = getParticipated();
        break;
      case 'unassigned' :
        $config = getUnassigned();
        break;
      case 'paused' :
        $config = getPaused();
        break;
      case 'to_revise' :
        $config = getToRevise();
        break;
      case 'to_reassign' :
        $config = getToReassign();
        break;
      case 'todo' :
      default : 
        $action = 'todo';
        $config = getToDo();
      break;
    }
    return $config;
  }
}

