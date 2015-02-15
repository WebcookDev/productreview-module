<?php

/**
 * This file is part of the ProductReview module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace AdminModule\ProductreviewModule;

use Nette\Forms\Form;
use WebCMS\ProductreviewModule\Entity\Product;

/**
 * Main controller
 *
 * @author Jakub Sanda <jakub.sanda@webcook.cz>
 */
class ProductsPresenter extends BasePresenter
{
    private $product;

    private $repository;

    protected function startup()
    {
    	parent::startup();

        $this->repository = $this->em->getRepository('WebCMS\ProductreviewModule\Entity\Product');
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
    }

    public function renderUpdate($idPage)
    {
        $this->reloadContent();

        $this->template->idPage = $idPage;
        $this->template->product = $this->product;
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

    protected function createComponentProductForm()
    {
        $form = $this->createForm();

        $form->addText('name', 'Name')->setRequired();
        $form->addTextArea('text', 'Text')->setAttribute('class', 'form-control editor');
        $form->addTextArea('specification', 'Specification')->setAttribute('class', 'form-control editor');

        $form->addCheckbox('hide', 'Hide');

        $form->addSubmit('submit', 'Save')->setAttribute('class', 'btn btn-success');
        $form->onSuccess[] = callback($this, 'productFormSubmitted');
 
        if (is_object($this->product)) {
            $form->setDefaults($this->product->toArray());
        }
        
        return $form;
    }

    public function productFormSubmitted($form)
    {
        $values = $form->getValues();

        if (!is_object($this->product)) {
            $this->product = new Product;
            $this->em->persist($this->product);
        } else {
            // delete old photos and save new ones
            $qb = $this->em->createQueryBuilder();
            $qb->delete('WebCMS\ProductreviewModule\Entity\Photo', 'l')
                    ->where('l.product = ?1')
                    ->setParameter(1, $this->product)
                    ->getQuery()
                    ->execute();
        }
        
        foreach ($values as $key => $value) {
            $setter = 'set' . ucfirst($key);
            $this->product->$setter($value);
        }

        if (array_key_exists('files', $_POST)) {
            $counter = 0;
            if(array_key_exists('fileDefault', $_POST)) $default = intval($_POST['fileDefault'][0]) - 1;
            else $default = -1;
            
            foreach($_POST['files'] as $path){

                $photo = new \WebCMS\ProductreviewModule\Entity\Photo;
                $photo->setName($_POST['fileNames'][$counter]);
                
                if($default === $counter){
                    $photo->setMain(TRUE);
                }else{
                    $photo->setMain(FALSE);
                }
                
                $photo->setPath($path);
                $photo->setProduct($this->product);
                $photo->setCreated(new \DateTime);

                $this->em->persist($photo);

                $counter++;
            }
        }

        $this->em->flush();
        $this->flashMessage('Product has been added/updated.', 'success');
        
        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

}