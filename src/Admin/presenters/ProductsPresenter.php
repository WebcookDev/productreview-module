<?php

/**
 * This file is part of the ProductReview module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace AdminModule\ProductreviewModule;

use Nette\Forms\Form;
use WebCMS\ProductreviewModule\Entity\Product;
use WebCMS\ProductreviewModule\Entity\Accessoriescategory;
use WebCMS\ProductreviewModule\Entity\Accessory;

/**
 * Main controller
 *
 * @author Jakub Sanda <jakub.sanda@webcook.cz>
 */
class ProductsPresenter extends BasePresenter
{
    private $product;

    private $repository;

    private $accessory;

    private $accessoriescategory;

    private $accessoryRepository;

    private $accessoriescategoryRepository;

    private $farben;

    private $materialen;

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
        $grid = $this->createGrid($this, $name, "\WebCMS\ProductreviewModule\Entity\Product", null, array(
            'page = '.$this->actualPage->getId()
        ));


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
        $this->accessoriescategory = $this->accessoriescategoryRepository->findBy(array(
            'page' => $this->actualPage
        ));
        $this->materialen = $this->accessoryRepository->findBy(array(
            'type' => 0,
            'page' => $this->actualPage
        ));
        $this->farben = $this->accessoryRepository->findBy(array(
            'type' => 1,
            'page' => $this->actualPage
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

    protected function createComponentAccessoriesCategoryGrid($name)
    {
        $grid = $this->createGrid($this, $name, "\WebCMS\ProductreviewModule\Entity\Accessoriescategory", null, array(
            'page = '.$this->actualPage->getId()
        ));

        $grid->addColumnText('name', 'Name')->setSortable();

        $grid->addActionHref("updateCategory", 'Edit', 'updateCategory', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax')));
        $grid->addActionHref("deleteCategory", 'Delete', 'deleteCategory', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-danger') , 'data-confirm' => 'Are you sure you want to delete this item?'));

        return $grid;
    }

    public function actionUpdateCategory($id, $idPage)
    {
        $this->accessoriescategory = $id ? $this->accessoriescategoryRepository->find($id) : "";
    }

    public function renderUpdateCategory($idPage)
    {
        $this->reloadContent();

        $this->template->idPage = $idPage;
        $this->template->accessoriescategory = $this->accessoriescategory;
    }

    public function actionDeleteCategory($id){

        $this->accessoriescategory = $this->accessoriescategoryRepository->find($id);
        $this->em->remove($this->accessoriescategory);
        $this->em->flush();
        
        $this->flashMessage('Accessories category has been removed.', 'success');
        
        if(!$this->isAjax()){
            $this->redirect('default', array(
                'idPage' => $this->actualPage->getId()
            ));
        }
    }

    protected function createComponentAccessoriesGrid($name)
    {
        $grid = $this->createGrid($this, $name, "\WebCMS\ProductreviewModule\Entity\Accessory", null, array(
            'page = '.$this->actualPage->getId()
        ));

        $grid->addColumnText('name', 'Name')->setSortable();

        $grid->addColumnText('type', 'Type')->setCustomRender(function($item) {
            return $item->getType() ? 'Farben' : 'Materialen';
        })->setSortable();

        $grid->addColumnText('file', 'File')->setCustomRender(function($item) {
            return '<img style="height:65px;width:65px;" src=".'.$item->getFile().'" alt="" />';
        });

        $grid->addColumnText('category', 'Category')->setCustomRender(function($item) {
            return $item->getAccessoriescategory()->getName();
        })->setSortable();

        $grid->addActionHref("updateAccessory", 'Edit', 'updateAccessory', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax')));
        $grid->addActionHref("deleteAccessory", 'Delete', 'deleteAccessory', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-danger') , 'data-confirm' => 'Are you sure you want to delete this item?'));

        return $grid;
    }

    public function actionUpdateAccessory($id, $idPage)
    {
        $this->accessory = $id ? $this->accessoryRepository->find($id) : "";
    }

    public function renderUpdateAccessory($idPage)
    {
        $this->reloadContent();

        $this->template->idPage = $idPage;
        $this->template->accessory = $this->accessory;
    }

    protected function createComponentProductForm()
    {
        $form = $this->createForm();

        $form->addText('name', 'Name')->setRequired();
        $form->addTextArea('text', 'Text')->setAttribute('class', 'form-control editor');
        $form->addTextArea('specification', 'Specification')->setAttribute('class', 'form-control editor');

        $form->addCheckbox('hide', 'Hide');

        $form->addHidden('accessories');

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

        $this->product->setPage($this->actualPage);

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

        if (array_key_exists('accessoriesPost', $_POST)) {
            $this->product->setAccessories(implode(',', $_POST['accessoriesPost']));
        } else {
            $this->product->setAccessories("");
        }

        $this->em->flush();
        $this->flashMessage('Product has been added/updated.', 'success');
        
        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

    protected function createComponentCategoryForm()
    {
        $form = $this->createForm();

        $form->addText('name', 'Name')->setRequired();

        $form->addSubmit('submit', 'Save')->setAttribute('class', 'btn btn-success');
        $form->onSuccess[] = callback($this, 'categoryFormSubmitted');
 
        if (is_object($this->accessoriescategory)) {
            $form->setDefaults($this->accessoriescategory->toArray());
        }
        
        return $form;
    }

    public function categoryFormSubmitted($form)
    {
        $values = $form->getValues();

        if (!is_object($this->accessoriescategory)) {
            $this->accessoriescategory = new Accessoriescategory;
            $this->em->persist($this->accessoriescategory);
        }
        
        foreach ($values as $key => $value) {
            $setter = 'set' . ucfirst($key);
            $this->accessoriescategory->$setter($value);
        }

        $this->accessoriescategory->setPage($this->actualPage);

        $this->em->flush();
        $this->flashMessage('Accessories category has been added/updated.', 'success');
        
        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

    protected function createComponentAccessoryForm()
    {
        $form = $this->createForm();

        $categories = $this->em->getRepository('\WebCMS\ProductreviewModule\Entity\Accessoriescategory')->findBy(array(
            'page' => $this->actualPage
        ));
        $categoriesForSelect = array();
        if ($categories) {
            foreach ($categories as $category) {
                $categoriesForSelect[$category->getId()] = $category->getName();
            }
        }

        $types = array(
            0 => 'Materialen',
            1 => 'Farben'
        );

        $form->addText('name', 'Name');
        $form->addSelect('type', 'Type')->setItems($types);
        $form->addSelect('accessoriescategory', 'Category')->setItems($categoriesForSelect);

        $form->addSubmit('submit', 'Save')->setAttribute('class', 'btn btn-success');
        $form->onSuccess[] = callback($this, 'accessoryFormSubmitted');
 
        if (is_object($this->accessory)) {
            $form->setDefaults($this->accessory->toArray());
        }
        
        return $form;
    }

    public function accessoryFormSubmitted($form)
    {
        $values = $form->getValues();

        if (!is_object($this->accessory)) {
            $this->accessory = new Accessory;
            $this->em->persist($this->accessory);
        }
        
        foreach ($values as $key => $value) {
            if ($key == "accessoriescategory") {
                $value = $this->accessoriescategoryRepository->find($value);
            }
            $setter = 'set' . ucfirst($key);
            $this->accessory->$setter($value);
        }

        $this->accessory->setPage($this->actualPage);

        if (array_key_exists('files', $_POST)) {
            $this->accessory->setFile($_POST['files'][0]);
        }

        $this->em->flush();
        $this->flashMessage('Accessory has been added/updated.', 'success');
        
        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

}