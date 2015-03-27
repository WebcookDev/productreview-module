<?php

/**
 * This file is part of the ProductReview module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace FrontendModule\ProductreviewModule;

use Nette\Application\UI;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use WebCMS\ProductreviewModule\Entity\Product;
use WebCMS\ProductreviewModule\Entity\Accessoriescategory;
use WebCMS\ProductreviewModule\Entity\Accessory;

/**
 * Description of ToursPresenter
 *
 * @author Jakub Sanda <jakub.sanda@webcook.cz>
 */
class ProductsPresenter extends BasePresenter
{
    private $repository;

    private $product;

    private $products;

    private $accessories = array();

    private $accessoriescategory;

    private $accessoryRepository;

    private $accessoriescategoryRepository;
    
    protected function startup() 
    {
        parent::startup();

        $this->repository = $this->em->getRepository('WebCMS\ProductreviewModule\Entity\Product');
        $this->accessoriescategoryRepository = $this->em->getRepository('WebCMS\ProductreviewModule\Entity\Accessoriescategory');
        $this->accessoryRepository = $this->em->getRepository('WebCMS\ProductreviewModule\Entity\Accessory');
    }

    protected function beforeRender()
    {
        parent::beforeRender(); 
    }

    public function actionDefault($id)
    {
        $this->products = $this->repository->findBy(array(
            'page' => $this->actualPage
        ), array('productOrder' => 'ASC'));
        $this->accessoriescategory = $this->accessoriescategoryRepository->findBy(array(
            'page' => $this->actualPage
        ));

        $parameters = $this->getParameter();

        if (count($parameters['parameters']) > 0) {
            $slug = $parameters['parameters'][0];
            $this->product = $this->repository->findOneBy(array(
                'slug' => $slug
            ));
            
            if (!is_object($this->product)) {
                throw new \Nette\Application\BadRequestException();
            }

            $accessories = $this->product->getAccessories();

            foreach (explode(",", $accessories) as $accessory) {
                $this->accessories[] = $this->accessoryRepository->find($accessory);
            }
        }

        if (count($this->products) == 1) {
            $accessories = $this->products[0]->getAccessories();

            foreach (explode(",", $accessories) as $accessory) {
                $this->accessories[] = $this->accessoryRepository->find($accessory);
            }
        }
    }

    public function renderDefault($id)
    {   
        if ($this->product) {
            $this->template->accessoriescategory = $this->accessoriescategory;
            $this->template->accessories = $this->accessories;
            $this->template->product = $this->product;
            $this->template->setFile(APP_DIR . '/templates/productreview-module/Products/detail.latte');
        }

        if (count($this->products) == 1) {
            $this->template->accessoriescategory = $this->accessoriescategory;
            $this->template->accessories = $this->accessories;
        }

        $this->template->id = $id;
        $this->template->products = $this->products;
    }

    public function productsBox($context, $fromPage)
    {
        $template = $context->createTemplate();
        $template->products = $context->em->getRepository('WebCMS\ProductreviewModule\Entity\Product')->findBy(array(
            'hide' => false,
            'page' => $fromPage,
            'homepage' => true
        ));

        $template->productPage = $context->em->getRepository('WebCMS\Entity\Page')->findOneBy(array(
            'moduleName' => 'Productreview',
            'presenter' => 'Products'
        ));

        $template->link = $context->link(':Frontend:Productreview:Products:default', array(
            'id' => $fromPage->getId(),
            'path' => $fromPage->getPath(),
            'abbr' => $context->abbr
        ));

        $template->abbr = $context->abbr;
        $template->setFile(APP_DIR . '/templates/productreview-module/Products/productsBox.latte');

        return $template;  
    }


}
