<?php
class profiles extends Control
{

    public function getTable()
    {
        $getParam = (object)$_POST;

        //Valores iniciales , libreria flexigrid
        $var = $this->basic->setParamTable($_POST, 'id_profile');

        //Parametros Table

        $data = array();

        $data['page'] = $var->page;

        $data['rows'] = array();

        //Total rows

        $getTotalRows_sql = 'SELECT COUNT(P.`id_profile`) As count 
                                FROM `bm_profiles` P 
                               ';

        $getTotalRows_result = $this->conexion->queryFetch($getTotalRows_sql);

        if ($getTotalRows_result)
            $data['total'] = $getTotalRows_result[0]['count'];
        else {
            $data['total'] = 0;
        }

        //Rows

        $getRows_sql = "SELECT P.`id_profile`, P.`profile`,GROUP_CONCAT(HG.`name`)  As groupName
    FROM `bm_profiles` P
        LEFT JOIN `bm_profiles_permit` PP  USING(`id_profile`)
        LEFT JOIN `bm_host_groups` HG ON PP.`groupid`=HG.`groupid` WHERE HG.`name` IS NOT NULL GROUP BY P.`id_profile` $var->sortSql $var->limitSql";

        $getRows_result = $this->conexion->queryFetch($getRows_sql);

        if ($getRows_result) {
            foreach ($getRows_result as $key => $row) {

                $option = '<span id="toolbar" class="ui-widget-header ui-corner-all">';
                $option .= '<span id="toolbarSet">
                                <button id="structureProfile" onclick="$.structureProfile(' . $row['id_profile'] . ')" name="structureProfile">Estructura</button>
                                <button id="categoriesProfile" onclick="$.categoriesProfile(' . $row['id_profile'] . ')" name="categoriesProfile">Categorias</button>
                                <button id="valueProfile" onclick="$.valueProfile(' . $row['id_profile'] . ')" name="valueProfile">Valores</button>
                                <button id="paramProfile" onclick="$.paramProfile(' . $row['id_profile'] . ')" name="paramProfile">Parametros</button>
                                <button id="cloneProfile" onclick="$.cloneProfile(' . $row['id_profile'] . ')" name="cloneProfile">Clonar</button>
                            </span>';
                $option .= '</span>';

                $data['rows'][] = array(
                    'id' => $row['id_profile'],
                    'cell' => array(
                        $row['profile'],
                        $row['groupName'],
                        $option
                    )
                );
            }
        }

        echo $this->basic->jsonEncode($data);
    }

    public function createProfile()
    {
        $getParam = (object)$_POST;

        $result['status'] = false;

        if (isset($getParam->name) && isset($getParam->groupid) && ($getParam->name != '')) {

            if (is_array($getParam->groupid)) {

                $this->conexion->InicioTransaccion();

                $name = $this->conexion->quote($getParam->name);

                $insertProfileSQL = "INSERT INTO `bm_profiles` (`profile`)
                                        VALUE ($name)";

                $insertProfileRESULT = $this->conexion->query($insertProfileSQL);

                if ($insertProfileRESULT) {

                    $lastInsertId = $this->conexion->lastInsertId();

                    $insertProfilePermSQL = "INSERT INTO `bm_profiles_permit` (`id_profile`, `groupid`) VALUES ";

                    foreach ($getParam->groupid as $key => $groupid) {

                        if (is_numeric($groupid)) {
                            $insertProfilePermValue[] = "($lastInsertId,$groupid)";
                        }

                    }

                    $insertProfilePermRESULT = $this->conexion->query($insertProfilePermSQL . join(',', $insertProfilePermValue));

                    if ($insertProfilePermRESULT) {
                        $insertProfileCategoriesSQL = "INSERT INTO `bm_profiles_categories` (`id_profile`,`category`,`class`,`global`)
                        VALUE ($lastInsertId,$name,'GLOBAL','true')";

                        $insertProfileCategoriesRESULT = $this->conexion->query($insertProfileCategoriesSQL);

                        if ($insertProfileCategoriesRESULT) {
                            $result['status'] = true;
                        } else {
                            $result['error'] = '(ProfileCategories) Insert failed';
                        }

                    } else {
                        $result['error'] = '(permProfile) Insert failed';
                    }

                } else {
                    $result['error'] = '(profile) Insert failed';
                }

            } else {
                $result['error'] = '(paramProfile) group option';
            }

        }

        if ($result['status'] === true) {
            $this->conexion->commit();
        } else {
            $this->conexion->rollBack();
        }

        echo $this->basic->jsonEncode($result);
    }

    public function deleteProfile()
    {
        $getParam = (object)$_POST;

        $result['status'] = false;

        if (isset($getParam->id) && ($getParam->id != '')) {

            if (is_array($getParam->id)) {

                $deleteProfileSQL = "DELETE FROM `bm_profiles` WHERE `id_profile` IN ";

                $deleteProfileValueSQL = $this->conexion->arrayIN($getParam->id, false, true);

                $deleteProfileRESULT = $this->conexion->query($deleteProfileSQL . $deleteProfileValueSQL);

                if ($deleteProfileRESULT) {
                    $result['status'] = true;
                } else {
                    $result['error'] = '(permProfile) Insert failed';
                }

            } else {
                $result['error'] = '(paramProfile) id param is not array';
            }

        } else {
            $result['error'] = '(paramProfile) id param';
        }

        echo $this->basic->jsonEncode($result);
    }

    public function getTableCategories($idprofile)
    {
        $getParam = (object)$_POST;

        //Valores iniciales , libreria flexigrid
        $var = $this->basic->setParamTable($_POST, 'id_categories');

        //Parametros Table

        $data = array();

        $data['page'] = $var->page;

        $data['rows'] = array();

        //Total rows

        $getTotalRows_sql = 'SELECT COUNT(`id_categories`) As count 
                                FROM `bm_profiles_categories` 
                                WHERE `global` = "false" AND `id_profile` = ' . $idprofile;

        $getTotalRows_result = $this->conexion->queryFetch($getTotalRows_sql);

        if ($getTotalRows_result)
            $data['total'] = $getTotalRows_result[0]['count'];
        else {
            $data['total'] = 0;
        }

        //Rows

        $getRows_sql = "SELECT `id_categories`, `category`, `class`
                                FROM `bm_profiles_categories` 
                                WHERE `global` = 'false' AND `id_profile` = $idprofile $var->sortSql $var->limitSql";

        $getRows_result = $this->conexion->queryFetch($getRows_sql);

        if ($getRows_result) {
            foreach ($getRows_result as $key => $row) {

                $option = '<span id="toolbar" class="ui-widget-header ui-corner-all">';
                $option .= '<span id="toolbarSet">
                                <button id="paramCategory" onclick="$.getItem(' . $row['id_categories'] . ',\'param\')" name="paramCategory">Parametros</button>
                                <button id="monitorCategory" onclick="$.getItem(' . $row['id_categories'] . ',\'result\')" name="monitorCategory">Monitores</button>
                            </span>';
                $option .= '</span>';

                $data['rows'][] = array(
                    'id' => $row['id_categories'],
                    'cell' => array(
                        $row['category'],
                        $row['class'],
                        $option
                    )
                );
            }
        }

        echo $this->basic->jsonEncode($data);
    }

    public function createCategory($profileid)
    {
        $getParam = (object)$_POST;

        $result['status'] = false;

        if (isset($getParam->name) && isset($getParam->class) && ($getParam->name != '')) {

            if (is_numeric($profileid)) {

                $name = $this->conexion->quote($getParam->name);
                $class = $this->conexion->quote($getParam->class);

                $insertSQL = "INSERT INTO `bm_profiles_categories` ( `id_profile`, `category`, `class`)
                VALUES
                    ( $profileid, $name, $class);
                ";

                $insertRESULT = $this->conexion->query($insertSQL);

                if ($insertRESULT) {

                    $lastInsertId = $this->conexion->lastInsertId();

                    $result['status'] = true;

                } else {
                    $result['error'] = '(category) Insert failed';
                }

            } else {
                $result['error'] = '(category) error param';
            }

        }

        echo $this->basic->jsonEncode($result);
    }

    public function deleteCategories()
    {
        $getParam = (object)$_POST;

        $result['status'] = false;

        if (isset($getParam->id) && ($getParam->id != '')) {

            if (is_array($getParam->id)) {

                $deleteProfileSQL = "DELETE FROM `bm_profiles_categories` WHERE `id_category` IN ";

                $deleteProfileValueSQL = $this->conexion->arrayIN($getParam->id, false, true);

                $deleteProfileRESULT = $this->conexion->query($deleteProfileSQL . $deleteProfileValueSQL);

                if ($deleteProfileRESULT) {
                    $result['status'] = true;
                } else {
                    $result['error'] = '(category) Insert failed';
                }

            } else {
                $result['error'] = '(category) id param is not array';
            }

        } else {
            $result['error'] = '(category) invalid param';
        }

        echo $this->basic->jsonEncode($result);
    }

    public function getTableItem($idcategory, $type)
    {
        $getParam = (object)$_POST;

        //Valores iniciales , libreria flexigrid
        $var = $this->basic->setParamTable($_POST, 'id_item');

        //Parametros Table

        $data = array();

        $data['page'] = $var->page;

        $data['rows'] = array();

        //Total rows

        $getTotalRows_sql = 'SELECT COUNT(`id_item`) As count 
                                FROM `bm_profiles_item` 
                                WHERE `id_categories` = ' . $idcategory . ' AND `type` = "' . $type . '"';

        $getTotalRows_result = $this->conexion->queryFetch($getTotalRows_sql);

        if ($getTotalRows_result)
            $data['total'] = $getTotalRows_result[0]['count'];
        else {
            $data['total'] = 0;
        }

        //Rows

        $getRows_sql = "SELECT `id_item`, `item`, `item_display`,`type_result`
                                FROM `bm_profiles_item` 
                                WHERE `id_categories` = $idcategory AND `type` = '$type' $var->sortSql $var->limitSql";

        $getRows_result = $this->conexion->queryFetch($getRows_sql);

        if ($getRows_result) {
            foreach ($getRows_result as $key => $row) {

                $option = '<span id="toolbar" class="ui-widget-header ui-corner-all">';
                $option .= '<span id="toolbarSet">
                                <button id="editItem" onclick="$.editItem(' . $row['id_item'] . ')" name="editItem">Editar</button>
                                <button id="deleteItem" onclick="$.deleteItem(' . $row['id_item'] . ')" name="deleteItem">Borrar</button>
                            </span>';
                $option .= '</span>';

                $data['rows'][] = array(
                    'id' => $row['id_item'],
                    'cell' => array(
                        $row['item'],
                        $row['item_display'],
                        $row['type_result'],
                        $option
                    )
                );
            }
        }

        echo $this->basic->jsonEncode($data);
    }

    public function createItem($idcategory)
    {
        $param = (object)$_POST;

        $result['status'] = false;

        if (isset($idcategory) && is_numeric($idcategory)) {

            if (!isset($param->name) && ($param->name == '') && !isset($param->display) && ($param->display == '')) {
                $result['error'] = 'Error invalid param';
            } else {

                $insertItenm = "INSERT INTO `bm_profiles_item` (`id_categories`, `item`, `item_display`, `type`, `type_result`, `default`, `report`, `unit`) VALUES ";

                if (isset($param->type) && ($param->type == 'param')) {

                    $insertValue = "($idcategory, '$param->name', '$param->display', 'param', '$param->typeData', '$param->default', 'false', NULL)";

                } elseif (isset($param->type) && ($param->type == 'result')) {

                    if ($param->report == 1) {
                        $report = 'true';
                    } else {
                        $report = 'false';
                    }

                    $insertValue = "($idcategory, '$param->name', '$param->display', 'result', '$param->typeData', '$param->default', '$report', '$param->unit')";
                }

                $insertResult = $this->conexion->query($insertItenm . $insertValue);

                if ($insertResult) {
                    $result['status'] = true;
                } else {
                    $result['error'] = 'Insert failed';
                }

            }

        } else {
            $result['error'] = 'Error id category not found';
        }

        echo $this->basic->jsonEncode($result);
    }

    public function deleteItem()
    {
        $getParam = (object)$_POST;

        $result['status'] = false;

        if (isset($getParam->id) && ($getParam->id != '')) {

            if (is_array($getParam->id)) {

                $deleteSQL = "DELETE FROM `bm_profiles_item` WHERE `id_item` IN ";

                $deleteValueSQL = $this->conexion->arrayIN($getParam->id, false, true);

                $deleteRESULT = $this->conexion->query($deleteSQL . $deleteValueSQL);

                if ($deleteRESULT) {
                    $result['status'] = true;
                } else {
                    $result['error'] = 'Insert failed';
                }

            } else {
                $result['error'] = 'id param is not array';
            }

        } else {
            $result['error'] = 'Invalid param';
        }

        echo $this->basic->jsonEncode($result);
    }

    public function getTableCategoriesValue($idprofile)
    {
        $getParam = (object)$_POST;

        //Valores iniciales , libreria flexigrid
        $var = $this->basic->setParamTable($_POST, 'id_categories');

        //Parametros Table

        $data = array();

        $data['page'] = $var->page;

        $data['rows'] = array();

        //Total rows

        $getTotalRows_sql = 'SELECT COUNT(`id_categories`) As count 
                                FROM `bm_profiles_categories` 
                                WHERE `global` = "false" AND `id_profile` = ' . $idprofile;

        $getTotalRows_result = $this->conexion->queryFetch($getTotalRows_sql);

        if ($getTotalRows_result)
            $data['total'] = $getTotalRows_result[0]['count'];
        else {
            $data['total'] = 0;
        }

        //Rows

        $getRows_sql = "SELECT `id_categories`, `category`, `class`
                                FROM `bm_profiles_categories` 
                                WHERE `global` = 'false' AND `id_profile` = $idprofile $var->sortSql $var->limitSql";

        $getRows_result = $this->conexion->queryFetch($getRows_sql);

        if ($getRows_result) {
            foreach ($getRows_result as $key => $row) {

                $option = '<span id="toolbar" class="ui-widget-header ui-corner-all">';
                $option .= '<span id="toolbarSet">
                                <button id="categoryValue" onclick="$.getCategoryValue(' . $row['id_categories'] . ')" name="categoryValue">Ingresar</button>
                            </span>';
                $option .= '</span>';

                $data['rows'][] = array(
                    'id' => $row['id_categories'],
                    'cell' => array(
                        $row['category'],
                        $row['class'],
                        $option
                    )
                );
            }
        }

        echo $this->basic->jsonEncode($data);
    }

    public function getTableCategoryValue($idcategories)
    {
        $getParam = (object)$_POST;

        //Valores iniciales , libreria flexigrid
        $var = $this->basic->setParamTable($_POST, 'id_category');

        //Parametros Table

        $data = array();

        $data['page'] = $var->page;

        $data['rows'] = array();

        //Total rows

        $getTotalRows_sql = 'SELECT COUNT(`id_category`) As count 
                                FROM `bm_profiles_category` 
                                WHERE `id_categories` = ' . $idcategories;

        $getTotalRows_result = $this->conexion->queryFetch($getTotalRows_sql);

        if ($getTotalRows_result)
            $data['total'] = $getTotalRows_result[0]['count'];
        else {
            $data['total'] = 0;
        }

        //Rows

        $getRows_sql = "SELECT `id_category`, `category`, `display`
                                FROM `bm_profiles_category` 
                                WHERE `id_categories` = $idcategories $var->sortSql $var->limitSql";

        $getRows_result = $this->conexion->queryFetch($getRows_sql);

        if ($getRows_result) {
            foreach ($getRows_result as $key => $row) {

                $option = '<span id="toolbar" class="ui-widget-header ui-corner-all">';
                $option .= '<span id="toolbarSet">
                                <button id="categoryValueEdit" onclick="$.editCategoryValue(' . $row['id_category'] . ')" name="categoryValueEdit">Editar</button>
                                <button id="categoryValueDelete" onclick="$.deleteCategoryValue(' . $row['id_category'] . ')" name="categoryValueDelete">Borrar</button>
                            </span>';
                $option .= '</span>';

                $data['rows'][] = array(
                    'id' => $row['id_category'],
                    'cell' => array(
                        $row['category'],
                        $row['display'],
                        $option
                    )
                );
            }
        }

        echo $this->basic->jsonEncode($data);
    }

    public function getFormCategoryValue()
    {
        $categoriesid = $_POST['id'];

        $valida = $this->protect->access_page('CONFIG_PROFILES_CATEGORY_VALUE');

        $this->plantilla->loadSec("configuracion/profiles/configCategoryValueForm", $valida, false);

        $varSite["formID"] = 'formNewCategoryValue';

        //Get Categories

        $getCategoriesSQL = 'SELECT PI.`id_categories`, PC.`class`, PC.`count`, PC.`creationCategory` , 
            PC.`creationMonitor`, PI.`id_item`, PI.`item`, PI.`item_display` , PI.`type`, PC.`id_profile`,PI.`default`
    FROM `bm_profiles_categories` PC LEFT OUTER JOIN `bm_profiles_item` PI ON PC.`id_categories`=PI.`id_categories` 
WHERE 
    PC.`global` = "false" AND 
    PC.`id_categories` = ' . $categoriesid;

        $getCategoriesRESULT = $this->conexion->queryFetch($getCategoriesSQL);

        if ($getCategoriesRESULT) {

            $categories = (object)$getCategoriesRESULT[0];

            // Get Count Category

            $getCountCategorySQL = 'SELECT `count` FROM `bm_profiles_category` PC 
                                        WHERE PC.`id_categories` = ' . $categoriesid;

            $getCountCategoryRESULT = $this->conexion->queryFetch($getCountCategorySQL);

            if ($getCountCategoryRESULT) {
                $countActive = array();
                foreach ($getCountCategoryRESULT as $key => $value) {
                    $countActive[$value['count']] = $value['count'];
                }

                $countAsig = count($getCountCategoryRESULT) + 1;

                for ($i = 1; $i <= count($getCountCategoryRESULT); $i++) {
                    if (!isset($countActive[$i])) {
                        $countAsig = $i;
                    }
                }
            } else {
                $countAsig = 1;
            }

            $category = str_replace("{COUNT}", $countAsig, $categories->creationCategory);

            $category = str_replace("{CLASS}", $categories->class, $category);

            $form = '<div class="row">
                <label for="category" class="col1">Categoria</label>
                <span class="col2 short">
                    <input readonly type="text" name="category" value="' . $category . '" id="category" class="input input-long ui-widget-content ui-corner-all" />
                </span>
            </div>';

            $form .= '<input type="hidden" name="count" value="' . $countAsig . '" />';

            $form .= '<div class="row">
                <label for="display" class="col1">Display</label>
                <span class="col2 short">
                    <input type="text" name="display" value="" id="display" class="input input-long ui-widget-content ui-corner-all" />
                </span>
            </div>';

            $form .= '<div class="row">
                <label for="default" class="col1">Default</label>
                <span class="col2 short">
                    <input type="text" name="default" value="" id="default" class="input input-long ui-widget-content ui-corner-all" />
                </span>
            </div>';

            foreach ($getCategoriesRESULT as $key => $value) {
                if ($value['type'] == 'param') {
                    $form .= '<div class="row">
                        <label for="param_' . $value['id_item'] . '" class="col1">' . $value['item_display'] . '</label>
                        <span class="col2 short">
                            <input type="text" name="param_' . $value['id_item'] . '" value="' . $value['default'] . '" id="' . $value['id_item'] . '" class="input input-long ui-widget-content ui-corner-all" />
                        </span>
                        </div>';
                }
            }

            $form .= '<div class="title"><span>Monitores</span></div>';

            ///Get Monitores

            $getMonitoresSQL = 'SELECT I.`id_item`, I.`descriptionLong`
                FROM `bm_items` I 
                    LEFT JOIN `bm_items_groups` IG ON I.`id_item`=IG.`id_item`
                    LEFT JOIN `bm_profiles_permit` PP ON PP.`groupid`= IG.`groupid`
                WHERE PP.`id_profile` = ' . $categories->id_profile . ' GROUP BY I.`id_item` ORDER BY I.`descriptionLong`;';

            $getMonitoresRESULT = $this->conexion->queryFetch($getMonitoresSQL);

            if ($getMonitoresRESULT) {
                $monitores = $this->basic->getOption($getMonitoresRESULT, 'id_item', 'descriptionLong', '-first-');
            } else {
                $monitores = '';
            }

            foreach ($getCategoriesRESULT as $key => $value) {
                if ($value['type'] == 'result') {

                    $display = str_replace("{CATEGORY}", $categories->class, $value['creationMonitor']);
                    $display = str_replace("{ITEM_DISPLAY}", $value['item_display'], $display);
                    $display = str_replace("{ITEM}", $value['item'], $display);

                    $form .= '<div class="row">
                        <label for="item_' . $value['id_item'] . '" class="col1">' . $display . '</label>
                        <span class="col2">
                        <select name="item_' . $value['id_item'] . '" id="item_' . $value['id_item'] . '" class="select">
                            <option value="0">Generar</option>' . $monitores . '
                        </select>
                        </span>
                        </div>';
                }
            }

            $varSite["form"] = $form;

        }

        $this->plantilla->set($varSite);

        echo $this->plantilla->get();
    }

    public function createCategoryValue($idcategories)
    {
        $param = (object)$_POST;

        $status = false;
        $continue = true;

        if (isset($idcategories) && is_numeric($idcategories)) {

            if (!isset($param->display) && ($param->display == '') && !isset($param->default) && ($param->default == '')) {
                $result['error'] = 'Error invalid param';
            } else {

                $this->conexion->InicioTransaccion();

                $insertCategorySQL = "INSERT INTO `bm_profiles_category` (`id_categories`, `category`, `display`, `default`, `count`) VALUES ";

                $category = $this->conexion->quote($param->category);
                $display = $this->conexion->quote($param->display);
                $default = $this->conexion->quote($param->default);

                $insertCategoryVALUE = "($idcategories, $category, $display, $default, $param->count)";

                $insertCategoryRESULT = $this->conexion->query($insertCategorySQL . $insertCategoryVALUE);

                if ($insertCategoryRESULT) {

                    $idCategory = $this->conexion->lastInsertId();

                    $itemParam = array();
                    $itemResult = array();

                    foreach ($param as $key => $value) {
                        if (preg_match("/^param_/i", $key)) {
                            $keyName = explode("_", $key, 2);
                            $itemParam[$keyName[1]] = $value;
                        } elseif (preg_match("/^item_/i", $key)) {
                            $keyName = explode("_", $key, 2);
                            $itemResult[$keyName[1]] = $value;
                        }
                    }

                    $insertItemSQL = 'INSERT INTO `bm_profiles_values` (`id_item`, `id_category`, `id_monitor`, `monitor_name`, `value`) VALUES ';

                    foreach ($itemParam as $key => $value) {
                        $valueQuote = $this->conexion->quote($value);
                        $insertItemVALUE[] = "( $key, $idCategory, 0, null, $valueQuote)";
                    }

                    //Insertar Monitores

                    //1.1 Get item value and param

                    $getItemSQL = 'SELECT `id_item`, PI.`item`, PI.`item_display` ,  `type_result`, `default`, `unit`, PG.`creationMonitor`,  PG.`class`, PG.`id_profile`
                                          FROM `bm_profiles_item` PI 
                                        LEFT JOIN `bm_profiles_categories` PG ON PI.`id_categories`=PG.`id_categories` 
                                        WHERE `id_item` IN (' . join(',', array_keys($itemResult)) . ');';

                    $getItemRESULT = $this->conexion->queryFetch($getItemSQL);

                    if ($getItemRESULT) {

                        foreach ($getItemRESULT as $key => $value) {
                            $idItem[$value['id_item']] = array(
                                "item" => $value['item'],
                                "item_display" => $value['item_display'],
                                "type" => $value['type_result'],
                                "default" => $value['default'],
                                "unit" => $value['unit'],
                                "creationMonitor" => $value['creationMonitor']
                            );
                            
                            $profileID = $value['id_profile'];
                        }

                        $insertMonitor = 'INSERT INTO `bm_items` 
                            (`name`, `description`, `descriptionLong`, `type_item`, 
                                `delay`, `history`, `trend`, `type_poller`, `unit`, `display`) VALUES ';

                        foreach ($itemResult as $key => $value) {

                            if ($value == 0 && isset($idItem[$key])) {

                                $monitor = str_replace("{CATEGORY}", $param->category, $idItem[$key]['creationMonitor']);
                                $monitor = str_replace("{CATEGORY_DISPLAY}", $param->display, $monitor);
                                $monitor = str_replace("{CATEGORY_DEFAULT}", $param->default, $monitor);
                                $monitor = str_replace("{ITEM}", $idItem[$key]['item'], $monitor);
                                $monitor = str_replace("{ITEM_DISPLAY}", $idItem[$key]['item_display'], $monitor);

                                $insertMonitorValue = "(`description`,'".$param->category."_".$idItem[$key]['item']."', '$monitor', '" . $idItem[$key]['type'] . "', 600, 90, 365, 'bsw_agent', '" . $idItem[$key]['unit'] . "', 'none');";

                                $insertMonitorRESULT = $this->conexion->query($insertMonitor . $insertMonitorValue);

                                if (!$insertMonitorRESULT) {
                                    $continue = false;
                                }

                                $value = $this->conexion->lastInsertId();
                                
                                $monitorIDArray[] = $value;

                            }

                            if ($continue == true) {
                                if (!isset($monitor)) {
                                    $monitor = 'none';
                                }
                                $insertItemVALUE[] = "( $key, $idCategory, $value, '$monitor', '$param->default')";
                            }

                        }

                        if ((count($insertItemVALUE) > 0) && $continue == true) {

                            $insertItemRESULT = $this->conexion->query($insertItemSQL . join(',', $insertItemVALUE));

                            if ($insertItemRESULT) {
                                
                                $getGroupIdProfileSQL = 'SELECT PP.`groupid` FROM `bm_profiles_permit` PP WHERE `id_profile` = '.$profileID;
                                
                                $getGroupIdProfileRESULT = $this->conexion->queryFetch($getGroupIdProfileSQL);
                                
                                if($getGroupIdProfileRESULT){
                                    
                                    $isertItemsGroupsSQL = 'INSERT IGNORE INTO `bm_items_groups` 
                                                                (`id_item`, `groupid`, `status`) VALUES ';
                                    
                                    
                                    foreach ($getGroupIdProfileRESULT as $key => $value) {
                                        
                                        $groupid = $value['groupid'];
                                        
                                        foreach ($monitorIDArray as $keyMonitor => $monitor) {
                                            $isertItemsGroupsValueSQL[] =  "($monitor,$groupid, 1)";
                                        }
                                        
                                    } 
                                    
                                    $isertItemsGroupsRESULT = $this->conexion->query($isertItemsGroupsSQL . join(',', $isertItemsGroupsValueSQL));

                                    if($isertItemsGroupsRESULT){
                                        $status = true;
                                    }                                 
                                }

                            } else {
                                $result['error'] = 'Error insert item profile';
                            }

                        } else {
                            $status = false;
                            $result['error'] = 'Error valid insert';
                        }

                    } else {
                        $result['error'] = 'Get item param failed';
                    }

                } else {
                    $result['error'] = 'Insert failed';
                }

                if ($status === true) {
                    $this->conexion->commit();
                } else {
                    $this->conexion->rollBack();
                }

            }

        } else {
            $result['error'] = 'Error id category not found';
        }

        $result['status'] = $status;

        echo $this->basic->jsonEncode($result);
    }

}
