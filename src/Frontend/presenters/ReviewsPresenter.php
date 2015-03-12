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

    private $markers = array();

    private $visitableMarkers = array();

    private $product;

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

    public function createComponentForm($name, $context = null, $fromPage = null) 
    {
        if($context != null){

            $form = new UI\Form();

            $form->getElementPrototype()->action = $context->link('default', array(
                'path' => $fromPage->getPath(),
                'abbr' => $context->abbr,
                'do' => 'form-submit'
            ));

            $form->setTranslator($context->translator);
            $form->setRenderer(new BootstrapRenderer);
            
            $form->getElementPrototype()->class = 'form-horizontal contact-agent-form ajax';
            
        }else{
            $form = $this->createForm('form-submit', 'default', $context);
        }


        $form->addText('name', 'Name:')->setRequired();
        $form->addText('email', 'E-mail:')->setRequired();
        $form->addTextArea('text', 'Nachricht:')->setRequired();
        $form->addHidden('productId');
        $form->addHidden('projectTitle');
        $form->addHidden('reviewId');

        $form->addSubmit('submit', 'Anfrage schicken')->setAttribute('class', 'btn btn-success');
        $form->onSuccess[] = callback($this, 'formSubmitted');

        return $form;
    }

    public function formSubmitted($form)
    {

        $values = $form->getValues();

        if (filter_var($values->email, FILTER_VALIDATE_EMAIL)) {
            $mail = new \Nette\Mail\Message;
            $infoMail = $this->settings->get('Info email', 'basic', 'text')->getValue();
            $mail->addTo($infoMail);

            $review = $this->repository->find($values->reviewId);

            $mail->addTo($review->getClientEmail());
            
            $domain = str_replace('www.', '', $this->getHttpRequest()->url->host);
            
            if($domain !== 'localhost') $mail->setFrom('no-reply@' . $domain);
            else $mail->setFrom('no-reply@test.cz'); // TODO move to settings

            $product = $this->productRepository->find($values->productId);

            $mailBody = '<h2>Poptávaný produkt: '.$product->getName().'</h2>';
            $mailBody .= '<h3>Projekt: '.$values->projectTitle.'</h3>';
            $mailBody .= '<p><strong>Dotazující: </strong>'.$values->name.'</p>';
            $mailBody .= '<p><strong>Email: </strong>'.$values->email.'</p>';
            $mailBody .= '<p><strong>Dotaz: </strong>'.$values->text.'</p>';

            $mail->setSubject('Poptávka produktu '.$product->getName());
            $mail->setHtmlBody($mailBody);

            try {
                $mail->send();  
                $this->flashMessage('Form has been sent', 'success');
            } catch (\Exception $e) {
                $this->flashMessage('Cannot send email.', 'danger');                    
            }
           
        } else {
            $this->flashMessage('Invalid email.', 'danger');           
        }

        
        $httpRequest = $this->getContext()->getService('httpRequest');

        $url = $httpRequest->getReferer();
        $url->appendQuery(array(self::FLASH_KEY => $this->getParam(self::FLASH_KEY)));

        $this->redirectUrl($url->absoluteUrl);
        
    }

    public function actionDefault($id)
    {
        $this->reviews = $this->repository->findBy(array(
            'page' => $this->actualPage
        ));
        $this->products = $this->productRepository->findBy(array(
            'language' => $this->language
        ));

        foreach ($this->reviews as $review) {
            if ($review->getLatitude() && $review->getLongtitude()) {
                    if ($review->getDefaultPhoto()) {
                        $picture = \WebCMS\Helpers\SystemHelper::thumbnail($review->getDefaultPhoto()->getPath(), 'projectFoto_');
                    } else {
                        $picture = "";
                    }

                    if ($review->getVisitable()) {
                        $this->visitableMarkers[] = array('id' => $review->getId(), 'productId'=> $review->getProduct()->getId(), 'latitude' => $review->getLatitude(), 'longtitude' => $review->getLongtitude(), 'title' => $review->getName(), 'text' => $review->getText(), 'visitable' => $review->getVisitable(), 'picture' => $picture);
                    }
                    $this->markers[] = array('id' => $review->getId(), 'productId'=> $review->getProduct()->getId(), 'latitude' => $review->getLatitude(), 'longtitude' => $review->getLongtitude(), 'title' => $review->getName(), 'text' => $review->getText(), 'visitable' => $review->getVisitable(), 'picture' => $picture);
            }
        }

        $parameters = $this->getParameter();

        if (count($parameters['parameters']) > 0) {
            $slug = $parameters['parameters'][0];
            $this->product = $this->productRepository->findOneBy(array(
                'slug' => $slug
            ));
            $this->reviews = $this->repository->findBy(array(
                'product' => $this->product
            ));

            $this->visitableMarkers = array();
            $this->markers = array();

            foreach ($this->reviews as $review) {
                if ($review->getLatitude() && $review->getLongtitude()) {

                    if ($review->getDefaultPhoto()) {
                        $picture = \WebCMS\Helpers\SystemHelper::thumbnail($review->getDefaultPhoto()->getPath(), 'projectFoto_');
                    } else {
                        $picture = "";
                    }

                    if ($review->getVisitable()) {
                        $this->visitableMarkers[] = array('id' => $review->getId(), 'productId'=> $review->getProduct()->getId(), 'latitude' => $review->getLatitude(), 'longtitude' => $review->getLongtitude(), 'title' => $review->getName(), 'text' => $review->getText(), 'visitable' => $review->getVisitable(), 'picture' => $picture);
                    }
                    $this->markers[] = array('id' => $review->getId(), 'productId'=> $review->getProduct()->getId(), 'latitude' => $review->getLatitude(), 'longtitude' => $review->getLongtitude(), 'title' => $review->getName(), 'text' => $review->getText(), 'visitable' => $review->getVisitable(), 'picture' => $picture);
                }
            }
        }
    }

    public function renderDefault($id)
    {   
        if ($this->product) {
            $this->template->list = array();
            $this->template->product = $this->product;
            $this->template->setFile(APP_DIR . '/templates/productreview-module/Reviews/detail.latte');
        }

        $this->template->id = $id;
        $this->template->markers = $this->markers;
        $this->template->visitableMarkers = $this->visitableMarkers;
        $this->template->reviews = $this->reviews;
        $this->template->products = $this->products;
        $this->template->projectForm = $this->createComponentForm('form', $this, $this->actualPage);
    }

    public function reviewsBox($context, $fromPage)
    {
        $template = $context->createTemplate();
        $template->reviews = $context->em->getRepository('WebCMS\ProductreviewModule\Entity\Review')->findBy(array(
            'hide' => false,
            'page' => $fromPage,
            'homepage' => true
        ));

        $template->reviewPage = $context->em->getRepository('WebCMS\Entity\Page')->findOneBy(array(
            'moduleName' => 'Productreview',
            'presenter' => 'Reviews'
        ));

        $template->link = $context->link(':Frontend:Productreview:Reviews:default', array(
            'id' => $fromPage->getId(),
            'path' => $fromPage->getPath(),
            'abbr' => $context->abbr
        ));

        $template->abbr = $context->abbr;
        $template->setFile(APP_DIR . '/templates/productreview-module/Reviews/reviewsBox.latte');

        return $template;  
    }

    public function mainReviewBox($context, $fromPage)
    {
        $template = $context->createTemplate();

        $parameters = $context->getParameter();

        if(count($parameters['parameters']) > 0){
            
            $product = $context->em->getRepository('WebCMS\ProductreviewModule\Entity\Product')->findOneBy(array(
                'slug' => $parameters['parameters'][0]
            ));

            $template->review = $context->em->getRepository('WebCMS\ProductreviewModule\Entity\Review')->findOneBy(array(
                'main' => true,
                'product' => $product
            ));

        }

        $template->reviewPage = $context->em->getRepository('WebCMS\Entity\Page')->findOneBy(array(
            'moduleName' => 'Productreview',
            'presenter' => 'Reviews'
        ));

        $template->link = $context->link(':Frontend:Productreview:Reviews:default', array(
            'id' => $fromPage->getId(),
            'path' => $fromPage->getPath(),
            'abbr' => $context->abbr
        ));

        $template->abbr = $context->abbr;
        $template->setFile(APP_DIR . '/templates/productreview-module/Reviews/mainReviewBox.latte');

        return $template;  
    }


}
