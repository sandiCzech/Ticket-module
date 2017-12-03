<?php

/**
 * This file is part of the Ticket module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace WebCMS\TicketModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as gedmo;

/**
 * @ORM\Entity()
 * @ORM\Table(name="ticket_Photo")
 */
class Photo extends \WebCMS\Entity\Entity
{

	/**
	 * @ORM\Column(type="text")
	 */
	private $title;

	/**
	 * @ORM\ManyToOne(targetEntity="Ticket")
	 * @ORM\JoinColumn(name="ticket_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	private $ticket;

	/**
	 * @ORM\Column(type="text")
	 */
	private $path;

	/**
	 * @ORM\Column(name="`default`",type="boolean")
	 */
	private $default;

  /**
	 * @ORM\Column(name="`default`",type="boolean")
	 */
	private $carousel;

	public function getTitle() {
		return $this->title;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function getTicket() {
		return $this->ticket;
	}

	public function setTicket($ticket) {
		$this->ticket = $ticket;
	}

	public function getPath() {
		return $this->path;
	}

	public function setPath($path) {
		$this->path = $path;
	}

	public function getDefault() {
		return $this->default;
	}

	public function setDefault($default) {
		$this->default = $default;
	}

  public function getCarousel() {
		return $this->carousel;
	}

	public function setCarousel($carousel) {
		$this->carousel = $carousel;
	}
}
