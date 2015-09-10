<?php
    class update extends Control {
        var $version = '210';
        
        public function index()
        {
            $valid = $this->conexion->query("ALTER TABLE `bm_profiles_item` CHANGE `type_result` `type_result` ENUM('float','string','text','log','combobox')  
                                        CHARACTER SET utf8  NOT NULL  DEFAULT 'string';");
            $valid = $valid && $this->conexion->query("ALTER TABLE `bm_profiles_item` CHANGE `default` `default` VARCHAR(255)  CHARACTER SET utf8  NULL  DEFAULT NULL;");
            $valid = $valid && $this->conexion->query("ALTER TABLE `bm_profiles_categories` ADD `sequenceCode` VARCHAR(11)  NOT NULL  DEFAULT '0'  AFTER `creationMonitorDisplay`;");
            $valid = $valid && $this->conexion->query("ALTER TABLE `bm_profiles_categories` MODIFY COLUMN `sequenceCode` VARCHAR(11) CHARACTER SET utf8 NOT NULL DEFAULT '0' AFTER `subcategory`;");
            $valid = $valid && $this->conexion->query("ALTER TABLE `bm_inform` ADD `location` VARCHAR(244)  NULL  DEFAULT NULL  AFTER `value`;");
            
            $valid = $valid && $this->conexion->query("UPDATE `bm_host_feature` SET `width` = '100' WHERE `id_feature` IN ('67',68,69,70,71,72)");
            $valid = $valid && $this->conexion->query("INSERT INTO `bm_option` (`id_option_form`, `option_group`, `id_option`, `option`, `selected`, `orden`, `type`) VALUES (NULL, 'type_data', 'combobox', 'combobox', 'false', 4, 'string');");
            
            $valid = $valid && $this->conexion->query("UPDATE `bm_profiles_categories` SET `sequenceCode` = 'D' WHERE `category` = 'Download';
                UPDATE `bm_profiles_categories` SET `sequenceCode` = '3' WHERE `category` = 'Ping';
                UPDATE `bm_profiles_categories` SET `sequenceCode` = '3' WHERE `category` = 'Ping';
                UPDATE `bm_profiles_categories` SET `sequenceCode` = 'K' WHERE `category` = 'Speedtest';
                UPDATE `bm_profiles_categories` SET `sequenceCode` = 'V' WHERE `category` = 'Streaming';
                UPDATE `bm_profiles_categories` SET `sequenceCode` = '4' WHERE `category` = 'WEB';
                UPDATE `bm_profiles_categories` SET `sequenceCode` = '7' WHERE `category` = 'YouTube';");
            if($valid) {
                echo 'OK';
            } else {
                echo 'NOK';
            }
                            
        }
    }
?>

