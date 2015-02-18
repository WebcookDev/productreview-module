<?php

/**
 * This file is part of the ProductReview module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace FrontendModule\ProductreviewModule;

use Nette\Application\UI;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use WebCMS\ProductreviewModule\Entity\Download;
use WebCMS\ProductreviewModule\Entity\Downloadcategory;

/**
 * Description of ToursPresenter
 *
 * @author Jakub Sanda <jakub.sanda@webcook.cz>
 */
class DownloadsPresenter extends BasePresenter
{
    private $download;

    private $downloads;

    private $category;

    private $categories;

    private $repository;

    private $categoryRepository;
    
    protected function startup() 
    {
        parent::startup();

        $this->repository = $this->em->getRepository('WebCMS\ProductreviewModule\Entity\Download');
        $this->categoryRepository = $this->em->getRepository('WebCMS\ProductreviewModule\Entity\Downloadcategory');
    }

    protected function beforeRender()
    {
        parent::beforeRender(); 
    }

    public function actionDefault($id)
    {
        $this->categories = $this->categoryRepository->findAll();

        $parameters = $this->getParameter();

        if (count($parameters['parameters']) > 0) {
            $categorySlug = $parameters['parameters'][0];
            $productSlug = $parameters['parameters'][1];
        }
    }

    public function renderDefault($id)
    {   
        // if ($this->product) {
        //     $this->template->accessoriescategory = $this->accessoriescategory;
        //     $this->template->accessories = $this->accessories;
        //     $this->template->product = $this->product;
        //     $this->template->setFile(APP_DIR . '/templates/productreview-module/Products/detail.latte');
        // }

        $this->template->id = $id;
        $this->template->categories = $this->categories;
    }


}
