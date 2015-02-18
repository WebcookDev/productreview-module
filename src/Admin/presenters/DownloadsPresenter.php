<?php

/**
 * This file is part of the ProductReview module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace AdminModule\ProductreviewModule;

use Nette\Forms\Form;
use WebCMS\ProductreviewModule\Entity\Product;
use WebCMS\ProductreviewModule\Entity\Download;

/**
 * Main controller
 *
 * @author Jakub Sanda <jakub.sanda@webcook.cz>
 */
class DownloadsPresenter extends BasePresenter
{
    private $download;

    private $repository;


    protected function startup()
    {
    	parent::startup();

        $this->repository = $this->em->getRepository('WebCMS\ProductreviewModule\Entity\Download');
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
        $grid = $this->createGrid($this, $name, "\WebCMS\ProductreviewModule\Entity\Product");

        $grid->addColumnText('name', 'Name')->setSortable();

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
        $this->product = $id ? $this->repository->find($id) : "";
        $this->accessoriescategory = $this->accessoriescategoryRepository->findAll();
        $this->materialen = $this->accessoryRepository->findBy(array(
            'type' => 0
        ));
        $this->farben = $this->accessoryRepository->findBy(array(
            'type' => 1
        ));
    }

    public function renderUpdate($idPage)
    {
        $this->reloadContent();

        $this->template->idPage = $idPage;
        $this->template->product = $this->product;
        $this->template->accessoriescategory = $this->accessoriescategory;
        $this->template->materialen = $this->materialen;
        $this->template->farben = $this->farben;
    }

    public function actionAddToHomepage($id, $idPage)
    {
        $this->product = $this->repository->find($id);
        $this->product->setHomepage($this->product->getHomepage() ? false : true);

        $this->em->flush();

        $this->flashMessage('Product has been changed', 'success');
        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

    public function actionDelete($id){

        $this->product = $this->repository->find($id);
        $this->em->remove($this->product);
        $this->em->flush();
        
        $this->flashMessage('Product has been removed.', 'success');
        
        if(!$this->isAjax()){
            $this->redirect('default', array(
                'idPage' => $this->actualPage->getId()
            ));
        }
    }

}