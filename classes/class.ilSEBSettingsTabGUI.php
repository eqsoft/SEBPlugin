<?php
/**
 * Copyright (c) 2017 Hochschule Luzern
 *
 * This file is part of the SEB-Plugin for ILIAS.
 
 * SEB-Plugin for ILIAS is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 
 * SEB-Plugin for ILIAS is distributed in the hope that
 * it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 
 * You should have received a copy of the GNU General Public License
 * along with SEB-Plugin for ILIAS.  If not,
 * see <http://www.gnu.org/licenses/>.
 *
 * The SEB-Plugin for ILIAS is a refactoring of a previous Plugin by Stefan
 * Schneider that can be found on Github
 * <https://github.com/hrz-unimr/Ilias.SEBPlugin>
 */

include_once 'class.ilSEBPlugin.php';

class ilSEBSettingsTabGUI {
    /** @var $object ilObjComponentSettings */
    protected $object;
    
    private $tpl;
    private $pl;
    private $conf;
    private $ctrl;
    private $user;
    private $tabs;
    private $obj_def;
    private $ref_id;
    
    function __construct()
    {
        /**
         * @var $ilCtrl ilCtrl
         * @var $ilUser ilObjUser
         * @var $ilTabs ilTabsGUI
         */
        global $DIC;
        
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->tabs = $DIC->tabs();
        $this->obj_def = $DIC['objDefinition'];
        
        $this->pl = ilSEBPlugin::getInstance();
        $this->conf = ilSEBConfig::getInstance();
        
        $this->ref_id = $_GET['ref_id'];
        $this->object = ilObjectFactory::getInstanceByRefId($this->ref_id);
        
        $this->ctrl->setParameter($this, 'ref_id', $this->ref_id);
    }
  
    function executeCommand() {
        $cmd = $this->ctrl->getCmd();
        
        switch($cmd)
        {
            case 'seb_settings':
                $this->showSettings();
                break;
            case 'save';
                $this->save();
                break;
            default:
                $this->defaultcmd();
        }
        
        
    }
    
    private function showSettings() {
        $this->initHeader();
        $form =  $this->initConfigurationForm();
        
        $this->tpl->setContent($form->getHTML());
        $this->tpl->getStandardTemplate();
        $this->tpl->show();
    }
    
    private function save() {
        $form = $this->initConfigurationForm();
        
        if ($form->checkInput()) {
            $seb_key_win = $form->getInput('seb_key_win');
            $seb_key_macos = $form->getInput('seb_key_macos');
            
            $success = $this->conf->saveObjectKeys($this->ref_id, $seb_key_win, $seb_key_macos);
            
            if ($success < 0) {
                ilUtil::sendFailure($this->pl->txt("save_failure"), true);
            } else if ($success == 0) {
                ilUtil::sendInfo($this->pl->txt("nothing_changed"), true);
            }else {
                ilUtil::sendSuccess($this->pl->txt("save_success"), true);
            }
            $this->showSettings();
        } else {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
            $this->tpl->show();
        }
    }
    
    private function initConfigurationForm() {
        include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
        
        global $DIC;
        
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormActionByClass('ilSEBSettingsTabGUI', 'save'));
        $form->setTitle($this->pl->txt('title_settings_form'));
        $form->setDescription($this->pl->txt('description_settings_form'));
        $form->addCommandButton('save', $DIC->language()->txt('save'));
        
        $keys = $this->conf->getObjectKeys($this->ref_id);
        
        $key_windows_txt = new ilTextInputGUI($this->pl->txt('key_windows'), 'seb_key_win');
        $key_windows_txt->setInfo($this->pl->txt('key_windows_info'));
        $key_windows_txt->setRequired(false);
        $key_windows_txt->setSize(50);
        $key_windows_txt->setValue($keys['seb_key_win']);
        $form->addItem($key_windows_txt);
        
        $key_macos_txt = new ilTextInputGUI($this->pl->txt('key_macos'), 'seb_key_macos');
        $key_macos_txt->setInfo($this->pl->txt('key_macos_info'));
        $key_macos_txt->setRequired(false);
        $key_macos_txt->setSize(50);
        $key_macos_txt->setValue($keys['seb_key_macos']);
        $form->addItem($key_macos_txt);
        
        return $form;
    }
    
    private function initHeader() {
        global $DIC;
        
        /* Add breadcrumbs */
        $DIC['ilLocator']->addRepositoryItems($this->ref_id);
        $DIC['ilLocator']->addItem($this->object->getTitle(), 
           $this->ctrl->getLinkTargetByClass(array(
                'ilRepositoryGUI',
                'ilObj' . $this->obj_def->getClassName($this->object->getType()) . 'GUI'
                ),
            "",
            $this->ref_id));
        $this->tpl->setLocator();
        
        /* Add title, description and icon of the current repositoryobject */
        $this->tpl->setTitle($this->object->getTitle());
        $this->tpl->setDescription($this->object->getDescription());
        $this->tpl->setTitleIcon(ilUtil::getTypeIconPath($this->object->getType(), $this->object->getId(), 'big'));
        
        /* Create and add backlink */
        $back_link = $this->ctrl->getLinkTargetByClass(array(
            'ilRepositoryGUI',
            'ilObj' . $this->obj_def->getClassName($this->object->getType()) . 'GUI'
        ));
        
        $class_name = $this->obj_def->getClassName($this->object->getType());
        $this->ctrl->setParameterByClass('ilObj' . $class_name . 'GUI', 'ref_id', $this->ref_id);
        $this->tabs->setBackTarget($DIC->language()->txt('back'), $back_link);
    }
}