<?php

/**
 * This file is part of the Ticket module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace FrontendModule\TicketModule;

use WebCMS\TicketModule\Entity\Ticket;


/**
 *
 * @author Jakub Sanda <jakub.sanda@webcook.cz>
 */
class TicketPresenter extends BasePresenter
{
	private $id;

	private $repository;

	private $categoriesRepository;

	private $ticket;

	private $tickets;

	private $categories;

	protected function startup()
    {
		parent::startup();

		$this->repository = $this->em->getRepository('\WebCMS\TicketModule\Entity\Ticket');
		$this->categoriesRepository = $this->em->getRepository('\WebCMS\TicketModule\Entity\Category');
	}

	protected function beforeRender()
    {
		parent::beforeRender();
	}

	public function actionDefault($id)
    {
		$this->tickets = $this->repository->findBy(array(), array('created' => 'DESC'));
		$this->categories = $this->categoriesRepository->findBy(array(), array('id' => 'DESC'));
	}

	public function renderDefault($id)
	{

		$detail = $this->getParameter('parameters');

		if (count($detail) > 0) {
		    $this->ticket = $this->repository->findOneBySlug($detail[0]);

		    if (!is_object($this->ticket)) {
				$this->redirect('default', array(
				    'path' => $this->actualPage->getPath(),
				    'abbr' => $this->abbr
				));
		    } else {
		    	$this->template->ticket = $this->ticket;
		    }
		}

		$this->template->categories = $this->categories;
		$this->template->tickets = $this->tickets;
		$this->template->id = $id;
	}

}