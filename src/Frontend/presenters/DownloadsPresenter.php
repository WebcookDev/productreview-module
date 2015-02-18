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

    private $mainDownloads;

    private $category;

    private $categories;

    private $product;

    private $products;

    private $repository;

    private $categoryRepository;

    private $productRepository;

    private $downloadCounts = array();
    
    protected function startup() 
    {
        parent::startup();

        $this->repository = $this->em->getRepository('WebCMS\ProductreviewModule\Entity\Download');
        $this->categoryRepository = $this->em->getRepository('WebCMS\ProductreviewModule\Entity\Downloadcategory');
        $this->productRepository = $this->em->getRepository('WebCMS\ProductreviewModule\Entity\Product');
    }

    protected function beforeRender()
    {
        parent::beforeRender(); 
    }

    public function actionDefault($id)
    {
        $this->categories = $this->categoryRepository->findAll();
        $this->products = $this->productRepository->findAll();

        foreach ($this->categories as $category) {
            $downloads = $this->repository->findBy(array(
                'category' => $category
            ));

            $this->downloadCounts[] = count($downloads);
        }

        $this->mainDownloads = $this->repository->findBy(array(
            'main' => true
        ));

        $parameters = $this->getParameter();

        if (count($parameters['parameters']) > 0) {
            $categorySlug = $parameters['parameters'][0];
            

            $this->category = $this->categoryRepository->findOneBy(array(
                'slug' => $categorySlug
            ));

            $this->downloads = $this->repository->findBy(array(
                'category' => $this->category
            ));

            if (isset($parameters['parameters'][1])) {
                $productSlug = $parameters['parameters'][1];
                $this->product = $this->productRepository->findOneBy(array(
                    'slug' => $productSlug
                ));

                $this->downloads = $this->repository->findBy(array(
                    'category' => $this->category,
                    'product' => $this->product,
                ));
            }
        }
    }

    public function renderDefault($id)
    {   
        if ($this->category) {
            $this->template->downloads = $this->downloads;
            $this->template->category = $this->category;
            $this->template->products = $this->products;
            $this->template->product = $this->product;
            $this->template->setFile(APP_DIR . '/templates/productreview-module/Downloads/detail.latte');
        }

        $this->template->id = $id;
        $this->template->mainDownloads = $this->mainDownloads;
        $this->template->downloadCounts = $this->downloadCounts;
        $this->template->categories = $this->categories;
    }


}
