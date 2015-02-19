<?php

/**
 * This file is part of the ProductReview module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace AdminModule\ProductreviewModule;

use Nette\Forms\Form;
use WebCMS\ProductreviewModule\Entity\Product;
use WebCMS\ProductreviewModule\Entity\Download;
use WebCMS\ProductreviewModule\Entity\Downloadcategory;

/**
 * Main controller
 *
 * @author Jakub Sanda <jakub.sanda@webcook.cz>
 */
class DownloadsPresenter extends BasePresenter
{
    private $download;

    private $category;

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
        $grid = $this->createGrid($this, $name, "\WebCMS\ProductreviewModule\Entity\Download", null, array(
            'page = '.$this->actualPage->getId()
        ));

        $grid->addColumnText('name', 'Name')->setSortable();

        $grid->addColumnText('category', 'Category')->setCustomRender(function($item) {
            return $item->getCategory()->getName();
        })->setSortable();

        $grid->addColumnText('product', 'Product')->setCustomRender(function($item) {
            return $item->getProduct()->getName();
        })->setSortable();

        $grid->addColumnText('type', 'Type')->setCustomRender(function($item) {
            return $item->getType() ? 'Surroundings' : 'Product';
        })->setSortable();

        $grid->addColumnText('main', 'Added to main page')->setCustomRender(function($item) {
            return $item->getMain() ? 'yes' : 'no';
        })->setSortable();

        $grid->addActionHref("update", 'Edit', 'update', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax')));
        $grid->addActionHref("setMain", 'Set main', 'setMain', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax')));
        $grid->addActionHref("delete", 'Delete', 'delete', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-danger') , 'data-confirm' => 'Are you sure you want to delete this item?'));

        return $grid;
    }

    public function actionUpdate($id, $idPage)
    {
        $this->download = $id ? $this->repository->find($id) : "";
    }

    public function renderUpdate($idPage)
    {
        $this->reloadContent();

        $this->template->idPage = $idPage;
        $this->template->download = $this->download;
    }

    public function actionSetMain($id, $idPage)
    {
        $this->download = $this->repository->find($id);
        $this->download->setMain($this->download->getMain() ? false : true);

        $this->em->flush();

        $this->flashMessage('Download has been changed', 'success');
        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

    public function actionDelete($id){

        $this->download = $this->repository->find($id);
        $this->em->remove($this->download);
        $this->em->flush();
        
        $this->flashMessage('Download has been removed.', 'success');
        
        if(!$this->isAjax()){
            $this->redirect('default', array(
                'idPage' => $this->actualPage->getId()
            ));
        }
    }

    protected function createComponentForm()
    {
        $form = $this->createForm();

        $categories = $this->em->getRepository('\WebCMS\ProductreviewModule\Entity\Downloadcategory')->findBy(array(
            'page' => $this->actualPage
        ));
        $categoriesForSelect = array();
        if ($categories) {
            foreach ($categories as $category) {
                $categoriesForSelect[$category->getId()] = $category->getName();
            }
        }

        $products = $this->em->getRepository('\WebCMS\ProductreviewModule\Entity\Product')->findBy(array(
            'page' => $this->actualPage
        ));
        $productsForSelect = array();
        if ($products) {
            foreach ($products as $product) {
                $productsForSelect[$product->getId()] = $product->getName();
            }
        }

        $types = array(
            0 => 'Product',
            1 => 'Surroundings'
        );
        

        $form->addText('name', 'Name')->setRequired();
        $form->addSelect('category', 'Category')->setItems($categoriesForSelect);
        $form->addSelect('product', 'Product')->setItems($productsForSelect);
        $form->addSelect('type', 'Type')->setItems($types);
                
        $form->addCheckbox('hide', 'Hide');

        $form->addSubmit('submit', 'Save')->setAttribute('class', 'btn btn-success');
        $form->onSuccess[] = callback($this, 'tourFormSubmitted');
 
        if (is_object($this->download)) {
            $form->setDefaults($this->download->toArray());
        }
        
        return $form;
    }
    
    public function tourFormSubmitted($form)
    {
        $values = $form->getValues();

        if(!is_object($this->download)){
            $this->download = new Download;
            $this->em->persist($this->download);
        }else{
            // delete old photos and save new ones
            $qb = $this->em->createQueryBuilder();
            $qb->delete('WebCMS\ProductreviewModule\Entity\Downloadfile', 'l')
                    ->where('l.download = ?1')
                    ->setParameter(1, $this->download)
                    ->getQuery()
                    ->execute();
        }

        $category = $this->em->getRepository('\WebCMS\ProductreviewModule\Entity\Downloadcategory')->find($values->category);
        $product = $this->em->getRepository('\WebCMS\ProductreviewModule\Entity\Product')->find($values->product);
        
        $this->download->setName($values->name);
        $this->download->setCategory($category);
        $this->download->setProduct($product);
        $this->download->setType($values->type);
        $this->download->setHide($values->hide);
        $this->download->setPage($this->actualPage);
            
        if(array_key_exists('files', $_POST)){
            $counter = 0;
            if(array_key_exists('fileDefault', $_POST)) $default = intval($_POST['fileDefault'][0]) - 1;
            else $default = -1;
            
            foreach($_POST['files'] as $path){

                $photo = new \WebCMS\ProductreviewModule\Entity\Downloadfile;
                $photo->setName($_POST['fileNames'][$counter]);
                
                if($default === $counter){
                    $photo->setMain(TRUE);
                }else{
                    $photo->setMain(FALSE);
                }
                
                $photo->setPath($path);
                $photo->setDownload($this->download);
                $photo->setCreated(new \DateTime);

                $this->em->persist($photo);

                $counter++;
            }
        }

        $this->em->flush();
        $this->flashMessage('Download has been added/updated.', 'success');
        
        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

    protected function createComponentCategoriesGrid($name)
    {
        $grid = $this->createGrid($this, $name, "\WebCMS\ProductreviewModule\Entity\Downloadcategory", null, array(
            'page = '.$this->actualPage->getId()
        ));

        $grid->addColumnText('name', 'Name')->setSortable();

        $grid->addActionHref("updateCategory", 'Edit', 'updateCategory', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax')));
        $grid->addActionHref("deleteCategory", 'Delete', 'deleteCategory', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-danger') , 'data-confirm' => 'Are you sure you want to delete this item?'));

        return $grid;
    }

    public function actionUpdateCategory($id, $idPage)
    {
        $this->reloadContent();

        $this->category = $id ? $this->em->getRepository('\WebCMS\ProductreviewModule\Entity\Downloadcategory')->find($id) : "";

        $this->template->idPage = $idPage;
    }

    public function actionDeleteCategory($id){

        $this->category = $this->categoryRepository->find($id);
        $this->em->remove($this->category);
        $this->em->flush();
        
        $this->flashMessage('Category has been removed.', 'success');
        
        if(!$this->isAjax()){
            $this->redirect('default', array(
                'idPage' => $this->actualPage->getId()
            ));
        }
    }

    protected function createComponentCategoryForm()
    {
        $form = $this->createForm();

        $form->addText('name', 'Name')->setRequired();

        $form->addSubmit('submit', 'Save')->setAttribute('class', 'btn btn-success');
        $form->onSuccess[] = callback($this, 'categoryFormSubmitted');
 
        if (is_object($this->category)) {
            $form->setDefaults($this->category->toArray());
        }
        
        return $form;
    }
    
    public function categoryFormSubmitted($form)
    {
        $values = $form->getValues();

        if(!is_object($this->category)){
            $this->category = new Downloadcategory;
            $this->em->persist($this->category);
        }
        
        foreach ($values as $key => $value) {
            $setter = 'set' . ucfirst($key);
            $this->category->$setter($value);
        }

        $this->category->setPage($this->actualPage);

        $this->em->flush();
        $this->flashMessage('Category has been added/updated.', 'success');
        
        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

}