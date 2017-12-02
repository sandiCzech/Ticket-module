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

    private $ticket;

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

        $grid->addColumnText('name', 'Název')->setSortable()->setFilterText();
        $grid->addColumnText('date', 'Datum')->setSortable()->setFilterText();

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

        $categories = $this->em->getRepository('\WebCMS\TicketModule\Entity\Category')->findAll();


        foreach ($categories as $category) {
            $categoriesToSelect[$category->getId()] = $category->getName();
        }

        $form->addText('name', 'Název')
            ->setRequired('Jméno je povinné.');
        $form->addText('date', 'Datum');
        $form->addText('day', 'Den');
        $form->addText('place', 'Místo');
        $form->addText('price', 'Cena');
        $form->addCheckbox('carousel', 'Carousel');
        $form->addSelect('category', 'Kategorie', $categoriesToSelect);
        $form->addTextArea('text', 'Text')->setAttribute('class', array('editor'));

        if ($this->ticket) {
            $form->setDefaults($this->ticket->toArray());
        }

        $form->addSubmit('save', 'Save ticket');

        $form->onSuccess[] = callback($this, 'formSubmitted');

        return $form;
    }

    public function formSubmitted($form)
    {
        $values = $form->getValues();

        if (!$this->ticket) {
            $this->ticket = new Ticket;
            $this->em->persist($this->ticket);
        }

        /*if (array_key_exists('files', $_POST)) {

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
        }*/

        $this->ticket->setName($values->name);
        $this->ticket->setDate($values->date);
        $this->ticket->setDay($values->day);
        $this->ticket->setPlace($values->place);
        $this->ticket->setPrice($values->price);
        $this->ticket->setText($values->text);
        $this->ticket->setCarousel($values->carousel);
        $this->ticket->setCategory($values->category);

        if ($values->category) {
            $category = $this->em->getRepository('\WebCMS\TicketModule\Entity\Category')->find($values->category);
            $this->ticket->setCategory($category);
        }

        $this->ticket->setActive(true);

        $this->em->flush();

        $this->flashMessage('Ticket has been saved/updated.', 'success');

        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }


}