<?php
namespace Drupal\mt_quotecart\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\redirect\Entity\Redirect;
use Drupal\paragraphs\Entity\Paragraph;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Messenger;

//use Drupal\Core\Field\FieldItemList;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Response;


class QuotecartForm extends FormBase {

  public function getFormID() {
    return 'quoterequest_add';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    
    for($j=1;$j<20;$j++){
      $qtynum[$j] = $j;
    }
    

    if(!empty($_SESSION['quotecart'])){
      // print_r($_SESSION['quotecart']);
      $i=0;
     
       foreach($_SESSION['quotecart'] as $key=>$value){    
         global $base_url;
         $productObj = \Drupal\node\Entity\Node::load($key);
           $nodetitle = $productObj->get('title')->getString();


           $moption = [];
           $field_measurement_options = $productObj->get('field_measurement_options')->getValue();
          // print_r($field_measurement_options);
           // Loop through the result set.
           foreach ($field_measurement_options as $key1 => $pgraph) {
            /** @var \Drupal\paragraphs\Entity\Paragraph $pgraph_obj */
              $pgraph_obj = Paragraph::load($pgraph['target_id']);
              if($pgraph_obj) {
                $field_options_values =  $pgraph_obj->get('field_options_values')->getString();
                $moption[$pgraph['target_id']] =  $field_options_values;
              }
            }
        //accessories
        $query = \Drupal::entityQuery('node');
        $query->condition('status', 1);
        $query->condition('type', 'Accessories');
        $query->condition('field_product', $key);
        //$query->addTag(‘anonymous’);
        $listaccessories = $query->accessCheck(FALSE)->execute();
       // print_r($listaccessories);
        $accessories = [];
        if ($listaccessories) {
          foreach ($listaccessories as $k => $v) {
           // $title = $v->get('title')->getString();
            // $eid = $event->id();
             $accObj = \Drupal\node\Entity\Node::load($v);
             $title = $accObj->get('title')->getString();
             $accessories[$v] = $title;
          }
        }
      //  $quantity = '<input type="text" name="qty" value="1">';
        $remove = t('<a href="'.$base_url.'/removecartitem/'.$key.'"  class="button js-form-submit form-submit">Remove</a>');
      
        $form['qc'.$i]=[
          '#type' => 'fieldset',
          '#title' => $nodetitle,
        ];
        $form['qc'.$i]['item'.$i]['pname'.$i] = [          
          '#type' => 'hidden',          
          '#title' => t('Item'),
          '#default_value'=> $nodetitle, 
        ];
        if(!empty($moption)){
          $form['qc'.$i]['item'.$i]['measurement_options'.$i] = [
            '#type' => 'checkboxes',
            '#title' => t('Measurement Options'),
            '#options'=> $moption,
            '#attributes'=>['class'=>['measurement_options']], 
          ];
        }
        if(!empty($accessories)){
          $form['qc'.$i]['item'.$i]['accessories'.$i] = [
            '#type' => 'checkboxes',
            '#title' => t('Accessories'),
            '#options'=> $accessories,
            '#attributes'=>['class'=>['accessories']], 
          ];
        }
        $form['qc'.$i]['qty'.$i] = [
          '#type' => 'select',
          '#title' => t('Quantity'),
          '#options'=>$qtynum,
          '#attributes'=>['class'=>['quote_qty']], 
          '#required' => TRUE,
        ];
        $form['qc'.$i]['remove'.$i] = [
          '#type' => 'markup',
          '#markup' => $remove,
        ];
    $i++;
    
    }

/*
    $header = $this->getHeader();
    $options = $this->getItem();

    $form['table'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
      '#empty' => t('No Item found'),
      '#validated' => TRUE,
    ];
    //disable checkboxes
    foreach ($options as $key2 => $value2) {     
        $form['table'][$key2]['#disabled'] = TRUE;     
    }
    */

    $form['contact']=[
      '#type' => 'fieldset',
      '#title' => t('Contact Detail'),
    ];
    $form['contact']['name'] = [
      '#type' => 'textfield',
      '#title' => t('Name'),
      '#required' => TRUE,
    ];
    
    $form['contact']['email'] = [
      '#type' => 'textfield',
      '#title' => t('Email'),
      '#required' => TRUE,
    ];

    $form['contact']['organization'] = [
      '#type' => 'textfield',
      '#title' => t('Organization'),
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Send Quote Request'),
    ];
    
    }else{
        $form['emptyproduct'] = [
          '#type' => 'markup',
          '#markup' => t('Cart is empty'),
        ];
    }

    $form['#attached']['library'] = 'mt_quotecart/quotecart';

    return $form;
  }


  public function validateForm(array &$form, FormStateInterface $form_state) {
    /*Nothing to validate on this form*/

  }

  public function submitForm(array &$form, FormStateInterface $form_state) { 
    $values = $form_state->getValues(); 

    $email = $values['email']; 
    $organization = $values['organization'];

      $key = 'mt_quotecart';      
      $to = 'sales@spectrafy.com';  //'richard.m.beal@gmail.com';  

     
      $fullname = $values['name'];
      $label = "Quote Cart Request";

      $message[] = "<p>Dear Admin </p>";
      $message[] = "<p>I want to Quote Request for Below products :</p>";

      for($k=0; $k < count($_SESSION['quotecart']); $k++){
       $arr_qty = $values['qty'.$k];
       $arr_pname = $values['pname'.$k];
       $arr_measurement_options = $values['measurement_options'.$k];
       $arr_accessories = $values['accessories'.$k];

        $message[] = 'Product Name :'. $arr_pname;
        $message[] = 'Quantity :'. $arr_qty;

        $field_options_values ='';
        foreach($arr_measurement_options as $m => $av){
          if($av != 0){
            $pgraph_obj = Paragraph::load($av);
              if($pgraph_obj) {
                $field_options_values .=  $pgraph_obj->get('field_options_values')->getString().', ';               
              }
          }
        }
        $message[] = 'Measurement Options :'. $field_options_values;

        $ntitle ='';
        foreach($arr_accessories as $a => $v){
          if($v != 0){ 
            $accObj = \Drupal\node\Entity\Node::load($v);
            $ntitle .= $accObj->get('title')->getString().', ';
          }
        } 
        $message[] = 'Accessories :'. $ntitle;   

      }
      $message[] = '<p>Thanks :</p>'; 
      $message[] = "<p>My Contact Information :</p><p>Name: $fullname </p><p>Email :  $email </p><p>Organization :  $organization </p>";  


      $this->sendEmail_quote($key, $to, $message, $label,$email);

      \Drupal::messenger()->addMessage(t('Your quote request has been sent successfully'), 'error');

      return new \Symfony\Component\HttpFoundation\RedirectResponse('<front>');
  }


  public function sendEmail_quote($key, $to, $text, $label,$email) {
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'mt_quotecart';
    $params['message'] = $text;
    $params['title'] = $label;
    $params['subject'] = $label;
    $params['from'] = $email;
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = TRUE;

    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    if ($result['result'] != TRUE) {
      $message = t('There was a problem sending your email notification to @email.', ['@email' => $to]);
      \Drupal::messenger()->addMessage($message, 'error');
      \Drupal::logger('mail-log')->error($message);
      return;
    }

    $message = t('An email notification has been sent to @email ', ['@email' => $to]);
   // drupal_set_message($message);
   \Drupal::messenger()->addMessage($message, 'error');
    \Drupal::logger('mail-log')->notice($message);
  }
}
