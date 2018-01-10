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

    protected function createComponentTicketGrid($name)
    {
        $grid = $this->createGrid($this, $name, "\WebCMS\TicketModule\Entity\Ticket", array(array('by' => 'rank', 'dir' => 'ASC')), array());

        $grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);

        $grid->addColumnText('name', 'Název')->setSortable()->setFilterText();
        $grid->addColumnText('rank', 'Pořadí')->setSortable()->setFilterText();

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

        $ticket = $this->em->getRepository('\WebCMS\TicketModule\Entity\Ticket')->find($id);

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
        $form->addText('url', 'URL')
            ->setRequired('URL je povinné.');
        $form->addText('seoKeywords', 'SEO klíčová slova');
        $form->addTextArea('seoDescription', 'SEO popis');
        $form->addText('date', 'Datum')->setAttribute('class', array('datepicker'));
        $form->addText('day', 'Den');
        $form->addText('place', 'Místo');
        $form->addText('price', 'Cena');
        $form->addCheckbox('carousel', 'Carousel');
        $form->addText('rank', 'Pořadí')
            ->addConditionOn($form['carousel'], Form::EQUAL, true)
                ->addRule(Form::FILLED, 'Při přidání položky do Carouselu je pořadí povinné')
                ->addRule(Form::INTEGER, 'Pořadí musí být číslo');
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
        } else {
            // delete old photos and save new ones
            $qb = $this->em->createQueryBuilder();
            $qb->delete('WebCMS\TicketModule\Entity\Photo', 'l')
                ->where('l.ticket = ?1')
                ->setParameter(1, $this->ticket)
                ->getQuery()
                ->execute();
        }

        if(array_key_exists('files', $_POST)){
    			$counter = 0;
    			if(array_key_exists('fileDefault', $_POST)) $default = intval($_POST['fileDefault'][0]) - 1;
    			else $default = 0;

          if(array_key_exists('fileCarousel', $_POST)) $carousel = intval($_POST['fileCarousel'][0]) - 1;
                else $carousel = 0;

            foreach($_POST['files'] as $path){

                $photo = new \WebCMS\TicketModule\Entity\Photo;
                $photo->setTitle($_POST['fileNames'][$counter]);

                if($default === $counter){
                    $photo->setDefault(TRUE);
                }else{
                    $photo->setDefault(FALSE);
                }

                if($carousel === $counter){
                    $photo->setCarousel(TRUE);
                }else{
                    $photo->setCarousel(FALSE);
                }

                $photo->setPath($path);
                $photo->setTicket($this->ticket);

                $this->em->persist($photo);

                $counter++;
            }
        }

        $this->ticket->setName($values->name);
        $this->ticket->setUrl($values->url);
        $this->ticket->setRank($values->rank == "" ? null : $values->rank);
        $this->ticket->setDate(new \Nette\DateTime($values->date));
        $this->ticket->setDay($values->day);
        $this->ticket->setPlace($values->place);
        $this->ticket->setPrice($values->price);
        $this->ticket->setText($values->text);
        $this->ticket->setSeoDescription($values->seoDescription);
        $this->ticket->setSeoKeywords($values->seoKeywords);
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
