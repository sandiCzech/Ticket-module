<?php

/**
 * This file is part of the Ticket module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace WebCMS\TicketModule;

/**
 *
 * @author Jakub Sanda <sanda@webcook.cz>
 */
class Ticket extends \WebCMS\Module
{
	/**
	 * [$name description]
	 * @var string
	 */
    protected $name = 'Ticket';

    /**
     * [$author description]
     * @var string
     */
    protected $author = 'Jakub Sanda';

    /**
     * [$presenters description]
     * @var array
     */
    protected $presenters = array(
    		array(
    		    'name' => 'Ticket',
    		    'frontend' => true,
    		    'parameters' => true
    		),
        array(
            'name' => 'Category',
            'frontend' => false,
            'parameters' => true
        ),
    		array(
    		    'name' => 'Settings',
    		    'frontend' => false
    		)
    );

    public function __construct()
    {

    }
}
