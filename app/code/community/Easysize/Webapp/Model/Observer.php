<?php

	class Easysize_Webapp_Model_Observer extends Varien_Event_Observer{
		public function __construct(){}

		public function checkoutObserver($observer){
						
			$eso = Mage::getModel('core/cookie')->get("eso");			
			Mage::log('eso from cookie: ' . $eso );

			$quote = Mage::getSingleton('checkout/session')->getQuote();
	        $cartItems = $quote->getAllVisibleItems();

	        $arr = Array();
	        
	        $eso = Mage::getModel('core/cookie')->get("eso");
	        $eso = json_decode($eso);

	        $order_id = $observer->getEvent()->getOrder()->getId();

	        foreach ($eso as $key => $value){
	        	if($this->inTheCart($value->product_id)){
	        		$url = 'http://54.186.147.109/prod/web_app_v1.0/php/track.php';
					$data = array('pageview_id' => $value->skey,'value' => $order_id, 'action' => '19');

					$options = array(
					    'http' => array(
				        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				        'method'  => 'POST',
				        'content' => http_build_query($data),
				   		 ),
					);
					$context  = stream_context_create($options);
					$response = file_get_contents($url, false, $context);
					Mage::log($response);
	        	}
	        }

			Mage::getModel('core/cookie')->delete("eso");		

		}
		public function logCartAdd($observer){

			
			
 			$eso = Mage::getModel('core/cookie')->get("eso");	
 			$skey = Mage::getModel('core/cookie')->get('skey');	
			
			$product = Mage::getModel('catalog/product')
			    	->load(Mage::app()->getRequest()->getParam('product', 0));
			$id = $product->getId();
			
			$helper = Mage::helper('webapp/data');
			$bool = $helper->isAvailable($id);
			if($bool){				
				
				$eso = Mage::getModel('core/cookie')->get("eso");		

				$curr = Array('skey'=>$skey, 'product_id'=>$id);
				$arr = json_decode($eso);
				
				if($eso==false || $eso==null || $eso==""){
					$arr = Array();
				}
				array_push($arr, $curr);
				$eso = json_encode($arr);
				
				Mage::getSingleton('core/cookie')->set('eso', $eso);
				
			}
		}

		public function inTheCart($id){
			$quote = Mage::getSingleton('checkout/session')->getQuote();
			foreach($quote->getAllVisibleItems() as $item) {
			    if ($item->getData('product_id') == $id) {
			        return true;
			        break;
			    }
			}
			return false;
		}


	}