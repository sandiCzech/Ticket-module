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
 * @ORM\Table(name="ticket_Category")
 */
class Category extends \WebCMS\Entity\Entity
{

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;


    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }


}
