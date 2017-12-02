<?php

/**
 * This file is part of the University module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace AdminModule\UniversityModule;

use Nette\Forms\Form;
use WebCMS\UniversityModule\Entity\Subject;

/**
 *
 * @author Jakub Sanda <jakub.sanda@webcook.cz>
 */
class SubjectPresenter extends BasePresenter
{
    
    private $subject;

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

    protected function createComponentSubjectGrid($name)
    {
        $grid = $this->createGrid($this, $name, "\WebCMS\UniversityModule\Entity\Subject", null, array());

        $grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);

        $grid->addColumnText('name', 'Name')->setSortable()->setFilterText();

        $grid->addActionHref("update", 'Edit', 'update', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn' , 'btn-primary', 'ajax')));
        $grid->addActionHref("delete", 'Delete', 'delete', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-danger'), 'data-confirm' => 'Are you sure you want to delete this item?'));

        return $grid;
    }


    public function actionUpdate($id, $idPage)
    {
        if ($id) {
            $this->subject = $this->em->getRepository('\WebCMS\UniversityModule\Entity\Subject')->find($id);
        }
    }

    public function renderUpdate($idPage)
    {
        $this->reloadContent();

        $this->template->subject = $this->subject;
        $this->template->idPage = $idPage;
    }
    
    public function actionDelete($id){

        $subject = $this->em->getRepository('\WebCMS\UniversityModule\Entity\Subject')->find($id);

        $this->em->remove($subject);
        $this->em->flush();
        
        $this->flashMessage('Subject has been removed.', 'success');
        
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

        $form->addTextArea('perex', 'Perex')->setAttribute('class', array('editor'));
        $form->addTextArea('text', 'Text')->setAttribute('class', array('editor'));

        $form->addTextArea('textPlan', 'Text Plan')->setAttribute('class', array('editor'));
        $form->addText('urlPlan', 'Url Plan');
        $form->addTextArea('textSchedule', 'Text Schedule')->setAttribute('class', array('editor'));
        $form->addText('urlSchedule', 'Url Schedule');
        $form->addTextArea('textExperience', 'Text Experience')->setAttribute('class', array('editor'));
        $form->addTextArea('textRequirements', 'Text Requirements')->setAttribute('class', array('editor'));
        $form->addTextArea('textInternship', 'Text Internship')->setAttribute('class', array('editor'));

        $form->addCheckbox('highschool', 'Part of highschool program?');

        if ($this->subject) {
            $form->setDefaults($this->subject->toArray());
        }

        $form->addSubmit('save', 'Save subject');

        $form->onSuccess[] = callback($this, 'formSubmitted');

        return $form;
    }

    public function formSubmitted($form)
    {
        $values = $form->getValues();

        if (!$this->subject) {
            $this->subject = new Subject;
            $this->em->persist($this->subject);
        }

        if (array_key_exists('files', $_POST)) {

            $counter = 0;
            
            foreach($_POST['files'] as $path){

                $photo = new \WebCMS\UniversityModule\Entity\Photo;
                $photo->setTitle($_POST['fileNames'][$counter]);
                
                $photo->setPath($path);

                $this->subject->setPhoto($photo); 

                $this->em->persist($photo);

                $counter++;
            }
        } else {
            $this->subject->setPhoto(null); 
        }

        $this->subject->setName($values->name);
        $this->subject->setPerex($values->perex);
        $this->subject->setText($values->text);
        $this->subject->setTextPlan($values->textPlan); 
        $this->subject->setUrlPlan($values->urlPlan); 
        $this->subject->setTextSchedule($values->textSchedule); 
        $this->subject->setUrlSchedule($values->urlSchedule); 
        $this->subject->setTextExperience($values->textExperience); 
        $this->subject->setTextRequirements($values->textRequirements); 
        $this->subject->setTextInternship($values->textInternship); 
        
        $this->subject->setHighschool($values->highschool);   
        $this->subject->setActive(true);    

        $this->em->flush();

        $this->flashMessage('Subject has been saved/updated.', 'success');

        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

    
}
