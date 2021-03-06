<?php
/**
 * ProcessMaker Open Source Edition
 * Copyright (C) 2004 - 2008 Colosa Inc.23
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * For more information, contact Colosa Inc, 2566 Le Jeune Rd.,
 * Coral Gables, FL, 33134, USA, or email info@colosa.com.
 */

/*
  * @Author Erik Amaru Ortiz <erik@colosa.com>
  * @Date Aug 26th, 2009
  */

$request = $_POST['request'];

switch ($request) {
    case 'save':
        if (isset( $_POST['items'] )) {
            $items = $_POST['items'];
            $tmpfilename = $_SESSION['Current_Dynafom']['Parameters']['FILE'];
            G::LoadSystem( 'dynaformhandler' );

            $o = new dynaFormHandler( PATH_DYNAFORM . "{$tmpfilename}.xml" );

            $list_elements = explode( ',', $items );

            $e = Array ();
            foreach ($list_elements as $element) {
                $e[] = $o->getNode( $element );
            }

            $o->__cloneEmpty();

            foreach ($e as $e1) {
                $o->setNode( $e1 );
            }

        }
        break;
    case 'saveHidden':
        $tmpfilename = $_SESSION['Current_Dynafom']['Parameters']['FILE'];
        G::LoadSystem( 'dynaformhandler' );
        $o = new dynaFormHandler( PATH_DYNAFORM . "{$tmpfilename}.xml" );
        $hidden_items = Array ();

        $has_hidden_items = false;
        if (isset( $_POST['hidden'] )) {
            if ($_POST['hidden'] != '') {
                $has_hidden_items = true;

                $hidden_items = explode( ',', $_POST['hidden'] );
                $hidden_items_tmp = $hidden_items;
                $hidden_items = Array ();
                foreach ($hidden_items_tmp as $hItem) {
                    $tmp = explode( "@", $hItem );
                    $hidden_items[] = $tmp[1];
                }
                $hidden_items_tmp = implode( ',', $hidden_items );
            }
        }

        if ($has_hidden_items) {
            $hStr = '';
            foreach ($hidden_items as $hItem) {
                $hStr .= "hideRowById('$hItem'); ";
            }
            //echo 'something todo';
            //print_r($hidden_items);
            $msg = " @! Autogenerated by Processmaker weboot;  Do not modify this content, this is autogenerated alway when dynaform is updated ";

            if ($o->nodeExists( '___pm_boot_strap___' )) {
                $o->remove( '___pm_boot_strap___' );
            }
            $metaEncrypt = G::encrypt( $hidden_items_tmp, 'dynafieldsHandler' );
            $o->add( '___pm_boot_strap___', Array ('type' => 'javascript',"meta" => $metaEncrypt
            ), "/*$msg*/ $hStr" );
            echo $metaEncrypt;
        } else {
            //we must to remove the boot strap node;
            $o->remove( '___pm_boot_strap___' );
        }
        break;
    case 'showImportForm':
        require_once 'classes/model/Dynaform.php';
        require_once 'classes/model/Content.php';
        $uidDynafom = $_POST['DYN_UID'];

        $oCriteria = new Criteria( 'workflow' );
        $oCriteria->clearSelectColumns();
        $oCriteria->addSelectColumn( DynaformPeer::DYN_FILENAME );
        $oCriteria->addAsColumn( 'DYNA_NAME', 'C_DYNA.CON_VALUE' );
        $oCriteria->addAsColumn( 'PROC_NAME', 'C_PROC.CON_VALUE' );

        $oCriteria->addAlias("C_DYNA", "CONTENT");
        $oCriteria->addAlias("C_PROC", "CONTENT");

        $arrayCondition = array();
        $arrayCondition[] = array(DynaformPeer::DYN_UID, "C_DYNA.CON_ID");
        $oCriteria->addJoinMC($arrayCondition, Criteria::LEFT_JOIN);

        $arrayCondition = array();
        $arrayCondition[] = array(DynaformPeer::PRO_UID, "C_PROC.CON_ID");
        $oCriteria->addJoinMC($arrayCondition, Criteria::LEFT_JOIN);

        $oCriteria->add( 'C_DYNA.CON_LANG', SYS_LANG );
        $oCriteria->add( 'C_DYNA.CON_CATEGORY', 'DYN_TITLE' );

        $oCriteria->add( 'C_PROC.CON_LANG', SYS_LANG );
        $oCriteria->add( 'C_PROC.CON_CATEGORY', 'PRO_TITLE' );

        $oCriteria->add( DynaformPeer::DYN_UID, $uidDynafom, Criteria::NOT_EQUAL);
        
        $oCriteria->addAscendingOrderByColumn ('PROC_NAME');
        $oCriteria->addAscendingOrderByColumn ('DYNA_NAME');
        $oDataset = DynaformPeer::doSelectRS( $oCriteria );
        $oDataset->setFetchmode( ResultSet::FETCHMODE_ASSOC );
        $oDataset->next();
        $aRow = $oDataset->getRow();


        $select = '<select name="form[IMPORT_DYNA]" id="form[IMPORT_DYNA]"  width="300" style="width: 300px" class="module_app_input___gray">';
        $selectGroup = '';
        while (is_array( $aRow )) {
            if ($selectGroup != $aRow['PROC_NAME']) {
                if ($selectGroup != '') {
                    $select .= '</optgroup>';
                }
                $selectGroup = $aRow['PROC_NAME'];
                $select .= '<optgroup label="' . $aRow['PROC_NAME'] . '">';
            }
            $select .= '<option value="' . $aRow['DYN_FILENAME'] . '">' . $aRow['DYNA_NAME'] . '</option>';
            $oDataset->next();
            $aRow = $oDataset->getRow();
        }

        $select .= '</optgroup></select>';
        $html = '<div id="importForm"><table width="100%" cellspacing="3" cellpadding="3" border="0">
                    <tbody>
                        <tr>
                            <td align="" colspan="2" style="background: none repeat scroll 0 0 #E0E7EF; border-bottom: 1px solid #C3D1DF;
                                color: #000000; font-weight: bold; padding-left: 5px; text-shadow: 0 1px 0 #FFFFFF;
                                font: 8px">
                                <span>' . G::LoadTranslation('ID_SELECT_DYNAFORM_IMPORT') . '</span>
                            </td>
                        </tr>
                        <tr>
                            <td width="40%" style="font: 11px/180% sans-serif,MiscFixed; color: #808080; text-align: right;">
                                <label for="form[IMPORT_DYNA]">' . G::LoadTranslation('ID_DYNAFORM') . '</label>
                            </td>
                            <td width="615" class="FormFieldContent">' . $select . '
                            </td>
                        </tr>
                        <tr>
                            <td align="center" colspan="2" class="FormButton">
                                <input type="button" onclick="dynaformEditor.import(document.getElementById(&#39;form[IMPORT_DYNA]&#39;).value);" value="' . G::LoadTranslation('ID_IMPORT') . '" class="module_app_button___gray " style=""> &nbsp;
                                <input type="button" onclick="panelImportDyna.remove();" value="' . G::LoadTranslation('ID_CANCEL') . '" class="module_app_button___gray " style="">
                            </td>
                        </tr>
                    </tbody>
                </table><div>';
        echo $html;
        break;
    case 'import':
        require_once 'classes/model/Dynaform.php';
        $uidDynafom = $_POST['DYN_UID'];
        $oCriteria = new Criteria( 'workflow' );
        $oCriteria->clearSelectColumns();
        $oCriteria->addSelectColumn( DynaformPeer::DYN_FILENAME );
        $oCriteria->add( DynaformPeer::DYN_UID, $uidDynafom, Criteria::EQUAL);
        $oDataset = DynaformPeer::doSelectRS( $oCriteria );
        $oDataset->setFetchmode( ResultSet::FETCHMODE_ASSOC );
        $oDataset->next();
        $aRow = $oDataset->getRow();

        $dynaFile = PATH_DYNAFORM . $aRow['DYN_FILENAME'] . '.xml';
        $importFile = PATH_DYNAFORM . $_POST['FILE'] . '.xml';

        $importFp = fopen ($importFile, "r+");
        $fileText = fread($importFp, filesize($importFile));
        fclose ( $importFp );
        $newFile = str_replace($_POST['FILE'], $aRow['DYN_FILENAME'], $fileText);

        $dynaFileTmp = PATH_DYNAFORM . $aRow['DYN_FILENAME'] . '_tmp0.xml';
        $dynafmFp = fopen ($dynaFileTmp,"w+");
        fwrite ( $dynafmFp, $newFile);
        fclose ( $dynafmFp );

        /*
        $dynafmFp = fopen ($dynaFile,"w+");
        fwrite ( $dynafmFp, $newFile);
        fclose ( $dynafmFp );
        */
        echo 'success';
        break;
    default:
        echo 'no request param.';
}

