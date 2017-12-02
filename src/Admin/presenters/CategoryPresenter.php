<?php

/**
 * This file is part of the University module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace AdminModule\UniversityModule;

use Nette\Forms\Form;
use WebCMS\UniversityModule\Entity\Category;

/**
 *
 * @author Jakub Sanda <jakub.sanda@webcook.cz>
 */
class CategoryPresenter extends BasePresenter
{
    
    private $category;

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

    protected function createComponentCategoryGrid($name)
    {
        $grid = $this->createGrid($this, $name, "\WebCMS\UniversityModule\Entity\Category", null, array());

        $grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);

        $grid->addColumnText('name', 'Name')->setSortable()->setFilterText();

        $grid->addActionHref("update", 'Edit', 'update', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn' , 'btn-primary', 'ajax')));
        $grid->addActionHref("delete", 'Delete', 'delete', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-danger'), 'data-confirm' => 'Are you sure you want to delete this item?'));

        return $grid;
    }


    public function actionUpdate($id, $idPage)
    {
        if ($id) {
            $this->category = $this->em->getRepository('\WebCMS\UniversityModule\Entity\Category')->find($id);
        }
    }

    public function renderUpdate($idPage)
    {
        $this->reloadContent();

        $this->template->idPage = $idPage;
    }
    
    public function actionDelete($id){

        $category = $this->em->getRepository('\WebCMS\UniversityModule\Entity\Category')->find($id);

        $this->em->remove($category);
        $this->em->flush();
        
        $this->flashMessage('Category has been removed.', 'success');
        
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

        if ($this->category) {
            $form->setDefaults($this->category->toArray());
        }

        $form->addSubmit('save', 'Save category');

        $form->onSuccess[] = callback($this, 'formSubmitted');

        return $form;
    }

    public function formSubmitted($form)
    {
        $values = $form->getValues();

        if (!$this->category) {
            $this->category = new Category;
            $this->em->persist($this->category);
        }

        $this->category->setName($values->name);

        $this->em->flush();

        $this->flashMessage('Category has been saved/updated.', 'success');

        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

    
}
