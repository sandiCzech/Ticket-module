<?php

/**
 * This file is part of the University module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace AdminModule\UniversityModule;

use Nette\Forms\Form;
use WebCMS\UniversityModule\Entity\Field;

/**
 *
 * @author Jakub Sanda <jakub.sanda@webcook.cz>
 */
class FieldPresenter extends BasePresenter
{
    
    private $field;

    protected function startup()
    {
    	parent::startup();
    }

    protected function beforeRender()
    {
	   parent::beforeRender();
    }

    public function actionDefault($idPage)
    {
    }

    public function renderDefault($idPage){
        $this->reloadContent();
        
        $this->template->idPage = $idPage;
    }

    protected function createComponentFieldGrid($name)
    {
        $grid = $this->createGrid($this, $name, "\WebCMS\UniversityModule\Entity\Field", array(array('by' => 'name', 'dir' => 'ASC')), array());

        $grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);

        $grid->addColumnText('name', 'Name')->setSortable()->setFilterText();

        $grid->addActionHref("update", 'Edit', 'update', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn' , 'btn-primary', 'ajax')));
        $grid->addActionHref("delete", 'Delete', 'delete', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-danger'), 'data-confirm' => 'Are you sure you want to delete this item?'));

        return $grid;
    }


    public function actionUpdate($id, $idPage)
    {
        if ($id) {
            $this->field = $this->em->getRepository('\WebCMS\UniversityModule\Entity\Field')->find($id);
        }
    }

    public function renderUpdate($idPage)
    {
        $this->reloadContent();

        $this->template->idPage = $idPage;
    }
    
    public function actionDelete($id){

        $field = $this->em->getRepository('\WebCMS\UniversityModule\Entity\Field')->find($id);

        $this->em->remove($field);
        $this->em->flush();
        
        $this->flashMessage('Field has been removed.', 'success');
        
        if(!$this->isAjax()){
            $this->forward('default', array(
                'idPage' => $this->actualPage->getId()
            ));
        }
    }

    public function createComponentForm($name)
    {
        $form = $this->createForm('form-submit', 'default', null);

        $form->addText('name', 'Name')
            ->setRequired('Name is mandatory.');

        $form->addCheckbox('isTeacher', 'Teachers field?');
        $form->addCheckbox('isMaterial', 'Materials field?');

        if ($this->field) {
            $form->setDefaults($this->field->toArray());
        }

        $form->addSubmit('save', 'Save field');

        $form->onSuccess[] = callback($this, 'formSubmitted');

        return $form;
    }

    public function formSubmitted($form)
    {
        $values = $form->getValues();

        if (!$this->field) {
            $this->field = new Field;
            $this->em->persist($this->field);
        }

        $this->field->setName($values->name);
        $this->field->setIsTeacher($values->isTeacher);
        $this->field->setIsMaterial($values->isMaterial);

        $this->em->flush();

        $this->flashMessage('Field has been saved/updated.', 'success');

        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

    
}
