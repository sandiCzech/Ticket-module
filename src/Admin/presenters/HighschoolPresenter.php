<?php

/**
 * This file is part of the University module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace AdminModule\UniversityModule;

use WebCMS\UniversityModule\Entity\Subject;

/**
 *
 * @author Jakub Sanda <jakub.sanda@webcook.cz>
 */
class HighschoolPresenter extends BasePresenter
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
        $grid = $this->createGrid($this, $name, "\WebCMS\UniversityModule\Entity\Subject", null, array(
            'highschool = true'
        ));

        $grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);

        $grid->addColumnText('name', 'Name')->setSortable()->setFilterText();

        return $grid;
    }

    
}
