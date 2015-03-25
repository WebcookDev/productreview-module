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
    }

    public function renderDefault($id)
    {   
        if ($this->product) {
            $this->template->accessoriescategory = $this->accessoriescategory;
            $this->template->accessories = $this->accessories;
            $this->template->product = $this->product;

            $countOfCarouselPhotos = $this->em->getRepository('WebCMS\ProductreviewModule\Entity\Photo')->findBy(array(
                'product' => $this->product,
                'inCarousel' => 1
            ));

            $this->template->seoTitle = $this->product->getMetaTitle();
            $this->template->seoDescription = $this->product->getMetaDescription();
            $this->template->seoKeywords = $this->product->getMetaKeywords();
            $this->template->countOfCarouselPhotos = count($countOfCarouselPhotos);
            $this->template->form = $this->createComponentForm('form', $this, $this->actualPage);
            $this->template->setFile(APP_DIR . '/templates/productreview-module/Products/detail.latte');
        }

        $this->template->id = $id;
        $this->template->products = $this->products;
    }

    public function createComponentForm($name, $context = null, $fromPage = null) 
    {
        if($context != null){

            $form = new UI\Form();

            $form->getElementPrototype()->action = $context->link('default', array(
                'path' => $fromPage->getPath(),
                'abbr' => $context->abbr,
                'parameters' => array($context->product->getSlug()),
                'do' => 'form-submit'
            ));

            $form->setTranslator($context->translator);
            $form->setRenderer(new BootstrapRenderer);
            
            $form->getElementPrototype()->class = 'form-horizontal contact-agent-form';
            
        }else{
            $form = new UI\Form();

            $form->getElementPrototype()->action = $this->link('default', array(
                'path' => $this->actualPage->getPath(),
                'abbr' => $this->abbr,
                'parameters' => array($this->product->getSlug()),
                'do' => 'form-submit'
            ));

            $form->setTranslator($this->translator);
            $form->setRenderer(new BootstrapRenderer);
            
            $form->getElementPrototype()->class = 'form-horizontal contact-agent-form';
        }

        $form->addText('name', 'Name:')->setRequired();
        $form->addText('email', 'E-mail:')
            ->addRule(UI\Form::EMAIL, 'Email is not valid')
            ->setRequired();
        $form->addTextArea('text', 'Text:')->setRequired();
        //if($this->product){
            $form->addHidden('productId', $this->product->getId());
        //}

        $form->addSubmit('submit', 'Send')->setAttribute('class', 'btn btn-success');
        $form->onSuccess[] = callback($this, 'formSubmitted');

        return $form;
    }

    public function formSubmitted($form)
    {

        $values = $form->getValues();

        $mail = new \Nette\Mail\Message;
        $infoMail = $this->settings->get('Info email', 'basic', 'text')->getValue();
        $mail->addTo($infoMail);
        
        $domain = str_replace('www.', '', $this->getHttpRequest()->url->host);
        
        if($domain !== 'localhost') $mail->setFrom('no-reply@' . $domain);
        else $mail->setFrom('no-reply@test.cz'); // TODO move to settings

        $product = $this->repository->find($values->productId);

        $mailBody = '<h2>Poptávaný produkt: '.$product->getName().'</h2>';
        $mailBody .= '<p><strong>Jméno: </strong>'.$values->name.'</p>';
        $mailBody .= '<p><strong>Email: </strong>'.$values->email.'</p>';
        $mailBody .= '<p><strong>Text zprávy: </strong>'.$values->text.'</p>';

        $mail->setSubject('Poptávka produktu '.$product->getName());
        $mail->setHtmlBody($mailBody);

        try {
            $mail->send();  
            $this->flashMessage('Form has been sent', 'success');
        } catch (\Exception $e) {
            $this->flashMessage('Cannot send email.', 'danger');                    
        }
       

        $httpRequest = $this->getContext()->getService('httpRequest');

        $url = $httpRequest->getReferer();
        $url->appendQuery(array(self::FLASH_KEY => $this->getParam(self::FLASH_KEY)));

        $this->redirectUrl($url->absoluteUrl);
        
    }

    public function productsBox($context, $fromPage)
    {
        $template = $context->createTemplate();
        $template->products = $context->em->getRepository('WebCMS\ProductreviewModule\Entity\Product')->findBy(array(
            'hide' => false,
            'page' => $fromPage,
            'homepage' => true
        ), array('productOrder' => 'ASC'));

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
