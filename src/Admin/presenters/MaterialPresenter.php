<?php

/**
 * This file is part of the University module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace AdminModule\UniversityModule;

use Nette\Forms\Form;
use WebCMS\UniversityModule\Entity\Material;

/**
 *
 * @author Jakub Sanda <jakub.sanda@webcook.cz>
 */
class MaterialPresenter extends BasePresenter
{
    
    private $material;

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

    protected function createComponentMaterialGrid($name)
    {
        $grid = $this->createGrid($this, $name, "\WebCMS\UniversityModule\Entity\Material", null, array(
            'page = '.$this->getParameter('idPage')
        ));

        $grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);

        $grid->addColumnText('title', 'Title')->setSortable()->setFilterText();
        $grid->addColumnText('author', 'Author')->setSortable()->setFilterText();
        $grid->addColumnText('url', 'Url')->setFilterText();

        $grid->addActionHref("update", 'Edit', 'update', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn' , 'btn-primary', 'ajax')));
        $grid->addActionHref("delete", 'Delete', 'delete', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-danger'), 'data-confirm' => 'Are you sure you want to delete this item?'));

        return $grid;
    }


    public function actionUpdate($id, $idPage)
    {
        if ($id) {
            $this->material = $this->em->getRepository('\WebCMS\UniversityModule\Entity\Material')->find($id);
        }
    }

    public function renderUpdate($idPage)
    {
        $this->reloadContent();

        $this->template->material = $this->material;
        $this->template->idPage = $idPage;
    }
    
    public function actionDelete($id){

        $material = $this->em->getRepository('\WebCMS\UniversityModule\Entity\Material')->find($id);

        $this->em->remove($material);
        $this->em->flush();
        
        $this->flashMessage('Material has been removed.', 'success');
        
        if(!$this->isAjax()){
            $this->forward('default', array(
                'idPage' => $this->actualPage->getId()
            ));
        }
    }

    public function createComponentForm($name)
    {
        $form = $this->createForm('form-submit', 'default', null);

        $options = $this->em->getRepository('\WebCMS\UniversityModule\Entity\Category')->findAll();

        $fields = $this->em->getRepository('\WebCMS\UniversityModule\Entity\Field')->findBy(array(
            'isMaterial' => true
        ), array());
        
        $optionsToSelect = array();
        foreach ($options as $option) {
            $optionsToSelect[$option->getId()] = $option->getName();
        }

        $fieldsToSelect = array();
        foreach ($fields as $field) {
            $fieldsToSelect[$field->getId()] = $field->getName();
        }

        $form->addText('title', 'Title')
            ->setRequired('Title is mandatory.');
        $form->addText('url', 'Url');
        $form->addText('author', 'Author');
        $form->addMultiSelect('categories', 'Categories', $optionsToSelect);
        $form->addMultiSelect('fields', 'Fields', $fieldsToSelect);
        $form->addTextArea('text', 'Text')->setAttribute('class', array('editor'));

        if ($this->material) {
            $form->setDefaults($this->material->toArray());
        }

        $form->addSubmit('save', 'Save material');

        $form->onSuccess[] = callback($this, 'formSubmitted');

        return $form;
    }

    public function formSubmitted($form)
    {
        $values = $form->getValues();

        $page = $this->em->getRepository('\WebCMS\Entity\Page')->find($this->getParameter('idPage'));

        if (!$this->material) {
            $this->material = new Material;
            $this->em->persist($this->material);
        }

        $this->material->setTitle($values->title);
        $this->material->setUrl($values->url); 
        $this->material->setAuthor($values->author);
        $this->material->setText($values->text); 
        $this->material->setPage($page);

        if ($values->categories) {
            foreach ($values->categories as $key => $value) {
                $category = $this->em->getRepository('\WebCMS\UniversityModule\Entity\Category')->find($value);

                $categories[] = $category;
            }

            $this->material->setCategories($categories); 
        }

        if ($values->fields) {
            foreach ($values->fields as $key => $value) {
                $field = $this->em->getRepository('\WebCMS\UniversityModule\Entity\Field')->find($value);

                $fields[] = $field;
            }

            $this->material->setFields($fields); 
        }

        if (array_key_exists('files', $_POST)) {

            $counter = 0;
            
            foreach($_POST['files'] as $path){

                $file = new \WebCMS\UniversityModule\Entity\File;
                $file->setTitle($_POST['fileNames'][$counter]);
                
                $file->setPath($path);

                $this->material->setFile($file); 

                $this->em->persist($file);

                $counter++;
            }
        } else {
            $this->material->setFile(null); 
        }
        
        $this->material->setActive(true);    

        $this->em->flush();

        $this->flashMessage('Material has been saved/updated.', 'success');

        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

    
}
