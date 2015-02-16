<?php

/**
 * This file is part of the ProductReview module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace AdminModule\ProductreviewModule;

use Nette\Forms\Form;
use WebCMS\ProductreviewModule\Entity\Product;
use WebCMS\ProductreviewModule\Entity\Review;

/**
 * Main controller
 *
 * @author Jakub Sanda <jakub.sanda@webcook.cz>
 */
class ReviewsPresenter extends BasePresenter
{
    private $review;

    private $product;

    private $repository;

    private $productRepository;

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

    public function actionDefault($idPage)
    {
       
    }

    public function renderDefault($idPage)
    {
        $this->reloadContent();
        $this->template->idPage = $idPage;
    }

    protected function createComponentGrid($name)
    {
        $grid = $this->createGrid($this, $name, "\WebCMS\ProductreviewModule\Entity\Review");

        $grid->addColumnText('name', 'Name')->setSortable();

        $grid->addColumnDate('date', 'Date')->setSortable();

        $grid->addColumnText('product', 'Product')->setCustomRender(function($item) {
            return $item->getProduct()->getName();
        })->setSortable();

        $grid->addColumnText('main', 'Main')->setCustomRender(function($item) {
            return $item->getMain() ? 'yes' : 'no';
        })->setSortable();

        $grid->addColumnText('visitable', 'Visitable')->setCustomRender(function($item) {
            return $item->getVisitable() ? 'yes' : 'no';
        })->setSortable();

        $grid->addColumnText('homepage', 'Added To homepage')->setCustomRender(function($item) {
            return $item->getHomepage() ? 'yes' : 'no';
        })->setSortable();

        $grid->addActionHref("update", 'Edit', 'update', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax')));
        $grid->addActionHref("addToHomepage", 'Add to homepage', 'addToHomepage', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax')));
        $grid->addActionHref("delete", 'Delete', 'delete', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-danger') , 'data-confirm' => 'Are you sure you want to delete this item?'));

        return $grid;
    }

    public function actionUpdate($id, $idPage)
    {
        $this->review = $id ? $this->repository->find($id) : "";
    }

    public function renderUpdate($idPage)
    {
        $this->reloadContent();

        $this->template->idPage = $idPage;
        $this->template->review = $this->review;
    }

    public function actionAddToHomepage($id, $idPage)
    {
        $this->review = $this->repository->find($id);
        $this->review->setHomepage($this->review->getHomepage() ? false : true);

        $this->em->flush();

        $this->flashMessage('Review has been changed', 'success');
        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

    public function actionDelete($id){

        $this->review = $this->repository->find($id);
        $this->em->remove($this->review);
        $this->em->flush();
        
        $this->flashMessage('Review has been removed.', 'success');
        
        if(!$this->isAjax()){
            $this->redirect('default', array(
                'idPage' => $this->actualPage->getId()
            ));
        }
    }

    protected function createComponentReviewForm()
    {
        $form = $this->createForm();

        $products = $this->em->getRepository('\WebCMS\ProductreviewModule\Entity\Product')->findAll();
        $productsForSelect = array();
        if ($products) {
            foreach ($products as $product) {
                $productsForSelect[$product->getId()] = $product->getName();
            }
        }

        $form->addText('name', 'Name')->setRequired();
        $form->addSelect('product', 'Product')->setItems($productsForSelect)->setRequired();
        $form->addText('date', 'Date')->setAttribute('class', array('datepicker'))->setRequired('Fill in date of this review.');
        $form->addText('price', 'Price')->setRequired();
        $form->addTextArea('text', 'Text')->setAttribute('class', 'form-control editor');

        $form->addCheckbox('visitable', 'Visitable');
        $form->addTextArea('clientText', 'Client text')->setAttribute('class', 'form-control editor');
        $form->addText('clientEmail', 'Client email')
            ->addConditionOn($form['visitable'], Form::EQUAL, TRUE)
            ->addRule(Form::FILLED, 'Fill in clients email.');

        $form->addText('latitude', 'Latitude');
        $form->addText('longtitude', 'Longitude');

        $form->addCheckbox('hide', 'Hide');
        $form->addCheckbox('main', 'Main');

        $form->addSubmit('submit', 'Save')->setAttribute('class', 'btn btn-success');
        $form->onSuccess[] = callback($this, 'reviewFormSubmitted');
 
        if (is_object($this->review)) {
            $form->setDefaults($this->review->toArray());
        }
        
        return $form;
    }

    public function reviewFormSubmitted($form)
    {
        $values = $form->getValues();

        if (!is_object($this->review)) {
            $this->review = new Review;
            $this->em->persist($this->review);
        } else {
            // delete old photos and save new ones
            $qb = $this->em->createQueryBuilder();
            $qb->delete('WebCMS\ProductreviewModule\Entity\Photoreview', 'l')
                    ->where('l.review = ?1')
                    ->setParameter(1, $this->review)
                    ->getQuery()
                    ->execute();
        }
        
        foreach ($values as $key => $value) {
            if ($key == "date") {
                $value = new \Nette\DateTime($value);
            }
            if ($key == "product") {
                $key = "product";
                $value = $this->productRepository->find($values->product);
            }
            $setter = 'set' . ucfirst($key);
            $this->review->$setter($value);
        }

        if (array_key_exists('files', $_POST)) {
            $counter = 0;
            if(array_key_exists('fileDefault', $_POST)) $default = intval($_POST['fileDefault'][0]) - 1;
            else $default = -1;
            
            foreach($_POST['files'] as $path){

                $photo = new \WebCMS\ProductreviewModule\Entity\Photoreview;
                $photo->setName($_POST['fileNames'][$counter]);
                
                if($default === $counter){
                    $photo->setMain(TRUE);
                }else{
                    $photo->setMain(FALSE);
                }
                
                $photo->setPath($path);
                $photo->setReview($this->review);
                $photo->setCreated(new \DateTime);

                $this->em->persist($photo);

                $counter++;
            }
        }

        $this->em->flush();
        $this->flashMessage('Review has been added/updated.', 'success');
        
        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

}