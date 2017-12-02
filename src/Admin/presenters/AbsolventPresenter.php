<?php

/**
 * This file is part of the University module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace AdminModule\UniversityModule;

use Nette\Forms\Form;
use WebCMS\UniversityModule\Entity\Absolvent;

/**
 *
 * @author Jakub Sanda <jakub.sanda@webcook.cz>
 */
class AbsolventPresenter extends BasePresenter
{
    
    private $absolvent;

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

    protected function createComponentAbsolventGrid($name)
    {
        $grid = $this->createGrid($this, $name, "\WebCMS\UniversityModule\Entity\Absolvent", null, array(
            'page = '.$this->getParameter('idPage')
        ));

        $grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);

        $grid->addColumnText('name', 'Name')->setSortable()->setFilterText();

        $grid->addActionHref("update", 'Edit', 'update', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn' , 'btn-primary', 'ajax')));
        $grid->addActionHref("delete", 'Delete', 'delete', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-danger'), 'data-confirm' => 'Are you sure you want to delete this item?'));

        return $grid;
    }


    public function actionUpdate($id, $idPage)
    {
        if ($id) {
            $this->absolvent = $this->em->getRepository('\WebCMS\UniversityModule\Entity\Absolvent')->find($id);
        }
    }

    public function renderUpdate($idPage)
    {
        $this->reloadContent();

        $this->template->absolvent = $this->absolvent;
        $this->template->idPage = $idPage;
    }
    
    public function actionDelete($id){

        $absolvent = $this->em->getRepository('\WebCMS\UniversityModule\Entity\Absolvent')->find($id);

        $this->em->remove($absolvent);
        $this->em->flush();
        
        $this->flashMessage('Absolvent has been removed.', 'success');
        
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
        $form->addText('department', 'Department');
        $form->addTextArea('perex', 'Perex')->setAttribute('class', array('editor'));
        $form->addTextArea('text', 'Text')->setAttribute('class', array('editor'));

        if ($this->absolvent) {
            $form->setDefaults($this->absolvent->toArray());
        }

        $form->addSubmit('save', 'Save absolvent');

        $form->onSuccess[] = callback($this, 'formSubmitted');

        return $form;
    }

    public function formSubmitted($form)
    {
        $values = $form->getValues();

        $page = $this->em->getRepository('\WebCMS\Entity\Page')->find($this->getParameter('idPage'));

        if (!$this->absolvent) {
            $this->absolvent = new Absolvent;
            $this->em->persist($this->absolvent);
        }

        if (array_key_exists('files', $_POST)) {

            $counter = 0;
            
            foreach($_POST['files'] as $path){

                $photo = new \WebCMS\UniversityModule\Entity\Photo;
                $photo->setTitle($_POST['fileNames'][$counter]);
                
                $photo->setPath($path);

                $this->absolvent->setPhoto($photo); 

                $this->em->persist($photo);

                $counter++;
            }
        } else {
            $this->absolvent->setPhoto(null); 
        }

        $this->absolvent->setName($values->name);
        $this->absolvent->setDepartment($values->department); 
        $this->absolvent->setPerex($values->perex); 
        $this->absolvent->setText($values->text); 
        $this->absolvent->setPage($page);
        
        $this->absolvent->setActive(true);    

        $this->em->flush();

        $this->flashMessage('Absolvent has been saved/updated.', 'success');

        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

    
}
