<?php

/**
 * This file is part of the Ticket module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace AdminModule\TicketModule;

use Nette\Forms\Form;
use WebCMS\TicketModule\Entity\Ticket;

/**
 *
 * @author Jakub Sanda <jakub.sanda@webcook.cz>
 */
class TicketPresenter extends BasePresenter
{

    private $teacher;

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

    protected function createComponentTeacherGrid($name)
    {
        $grid = $this->createGrid($this, $name, "\WebCMS\TicketModule\Entity\Ticket", array(array('by' => 'name', 'dir' => 'ASC')), array());

        $grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);

        $grid->addColumnText('name', 'Jméno')->setSortable()->setFilterText();

        $grid->addActionHref("update", 'Edit', 'update', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn' , 'btn-primary', 'ajax')));
        $grid->addActionHref("delete", 'Delete', 'delete', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-danger'), 'data-confirm' => 'Are you sure you want to delete this item?'));

        return $grid;
    }


    public function actionUpdate($id, $idPage)
    {
        if ($id) {
            $this->ticket = $this->em->getRepository('\WebCMS\TicketModule\Entity\Ticket')->find($id);
        }
    }

    public function renderUpdate($idPage)
    {
        $this->reloadContent();

        $this->template->ticket = $this->ticket;
        $this->template->idPage = $idPage;
    }

    public function actionDelete($id){

        $teacher = $this->em->getRepository('\WebCMS\TicketModule\Entity\Ticket')->find($id);

        $this->em->remove($ticket);
        $this->em->flush();

        $this->flashMessage('Ticket has been removed.', 'success');

        if(!$this->isAjax()){
            $this->forward('default', array(
                'idPage' => $this->actualPage->getId()
            ));
        }
    }

    public function createComponentForm($name)
    {
        $form = $this->createForm('form-submit', 'default', null);

        $options = $this->em->getRepository('\WebCMS\TicketModule\Entity\Field')->findBy(array(
            'isTeacher' => true
        ), array());


        foreach ($options as $option) {
            $optionsToSelect[$option->getId()] = $option->getName();
        }

        $form->addText('degreeBefore', 'Titul před jménem');
        $form->addText('firstname', 'Jméno')
            ->setRequired('Jméno je povinné.');
        $form->addText('lastname', 'Příjmení')
            ->setRequired('Příjmení je povinné.');
        $form->addText('degreeAfter', 'Titul za jménem');
        $form->addText('department', 'Department');
        $form->addMultiSelect('fields', 'Fields', $optionsToSelect);
        $form->addTextArea('perex', 'Perex')->setAttribute('class', array('editor'));
        $form->addTextArea('text', 'Text')->setAttribute('class', array('editor'));

        if ($this->teacher) {
            $form->setDefaults($this->teacher->toArray());
        }

        $form->addSubmit('save', 'Save teacher');

        $form->onSuccess[] = callback($this, 'formSubmitted');

        return $form;
    }

    public function formSubmitted($form)
    {
        $values = $form->getValues();

        $page = $this->em->getRepository('\WebCMS\Entity\Page')->find($this->getParameter('idPage'));

        if (!$this->teacher) {
            $this->teacher = new Teacher;
            $this->em->persist($this->teacher);
        }

        if (array_key_exists('files', $_POST)) {

            $counter = 0;

            foreach($_POST['files'] as $path){

                $photo = new \WebCMS\UniversityModule\Entity\Photo;
                $photo->setTitle($_POST['fileNames'][$counter]);

                $photo->setPath($path);

                $this->teacher->setPhoto($photo);

                $this->em->persist($photo);

                $counter++;
            }
        } else {
            $this->teacher->setPhoto(null);
        }

        $this->teacher->setDegreeBefore($values->degreeBefore);
        $this->teacher->setFirstname($values->firstname);
        $this->teacher->setLastname($values->lastname);
        $this->teacher->setDegreeAfter($values->degreeAfter);
        $this->teacher->setDepartment($values->department);
        $this->teacher->setPerex($values->perex);
        $this->teacher->setText($values->text);
        $this->teacher->setPage($page);

        if ($values->fields) {
            foreach ($values->fields as $key => $value) {
                $field = $this->em->getRepository('\WebCMS\UniversityModule\Entity\Field')->find($value);

                $fields[] = $field;
            }

            $this->teacher->setFields($fields);
        }


        $this->teacher->setActive(true);

        $this->em->flush();

        $this->flashMessage('Teacher has been saved/updated.', 'success');

        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }


}
