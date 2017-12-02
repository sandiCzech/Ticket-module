<?php

/**
 * This file is part of the University module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace AdminModule\UniversityModule;

/**
 * Description of
 *
 * @author Jakub Sanda <sanda@webcook.cz>
 */
class BasePresenter extends \AdminModule\BasePresenter
{	
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
}