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
            $mail->addTo($values->email);
            
            $domain = str_replace('www.', '', $this->getHttpRequest()->url->host);
            
            if($domain !== 'localhost') $mail->setFrom('no-reply@' . $domain);
            else $mail->setFrom('no-reply@test.cz'); // TODO move to settings

            $mailBody = '<p><strong>Dotazující: </strong>'.$values->name.'</p>';
            $mailBody .= '<p><strong>Email: </strong>'.$values->email.'</p>';
            $mailBody .= '<p><strong>Dotaz: </strong>'.$values->text.'</p>';

            $mail->setSubject('Dotaz na projekt');
            $mail->setHtmlBody($mailBody);

            try {
                $mail->send();  
                $this->flashMessage('Form has been sent', 'success');
            } catch (\Exception $e) {
                $this->flashMessage('Cannot send form.', 'danger');                    
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
        $this->reviews = $this->repository->findAll();
        $this->products = $this->productRepository->findAll();

        foreach ($this->reviews as $review) {
            if ($review->getLatitude() && $review->getLongtitude()) {
                if ($review->getVisitable()) {
                    $this->visitableMarkers[] = array('latitude' => $review->getLatitude(), 'longtitude' => $review->getLongtitude(), 'title' => $review->getName(), 'text' => $review->getText(), 'visitable' => $review->getVisitable());
                }
                $this->markers[] = array('latitude' => $review->getLatitude(), 'longtitude' => $review->getLongtitude(), 'title' => $review->getName(), 'text' => $review->getText(), 'visitable' => $review->getVisitable());
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
                    if ($review->getVisitable()) {
                        $this->visitableMarkers[] = array('latitude' => $review->getLatitude(), 'longtitude' => $review->getLongtitude(), 'title' => $review->getName(), 'text' => $review->getText(), 'visitable' => $review->getVisitable());
                    }
                    $this->markers[] = array('latitude' => $review->getLatitude(), 'longtitude' => $review->getLongtitude(), 'title' => $review->getName(), 'text' => $review->getText(), 'visitable' => $review->getVisitable());
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


}
