<?php

/**
 * This file is part of the ProductReview module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace FrontendModule\ProductreviewModule;

use Nette\Application\UI;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use WebCMS\ProductreviewModule\Entity\Product;
use WebCMS\ProductreviewModule\Entity\Review;

/**
 * Description of ToursPresenter
 *
 * @author Jakub Sanda <jakub.sanda@webcook.cz>
 */
class ReviewsPresenter extends BasePresenter
{
    private $repository;

    private $productRepository;

    private $review;

    private $reviews;

    private $products;
    
    protected function startup() 
    {
        parent::startup();

        $this->repository = $this->em->getRepository('WebCMS\ProductreviewModule\Entity\Review');
        $this->productRepository = $this->em->getRepository('WebCMS\ProductreviewModule\Entity\Product');
    }

    protected function beforeRender()
    {
        parent::beforeRender(); 
    }

    public function actionDefault($id)
    {
        $this->reviews = $this->repository->findAll();

        $parameters = $this->getParameter();

        if (count($parameters['parameters']) > 0) {
            $slug = $parameters['parameters'][0];
            $product = $this->productRepository->findOneBy(array(
                'slug' => $slug
            ));
            $this->reviews = $this->repository->findBy(array(
                'product' => $product
            ));
        }
    }

    public function renderDefault($id)
    {   
        // if ($this->reviews) {
        //     $this->template->reviews = $this->reviews;
        //     $this->template->setFile(APP_DIR . '/templates/productreview-module/Reviews/detail.latte');
        // }

        $this->template->id = $id;
        $this->template->reviews = $this->reviews;
    }


}
