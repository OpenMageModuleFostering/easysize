<?php

class EasySize_SizeGuide_Model_Observer {
    public function sendTrackingData($observer) {
        if(Mage::getModel('core/cookie')->get('es_cart_items') != false) {
            // Get all items in cart 
            $order = Mage::getModel('sales/order')->load($observer->getData('order_ids')[0]);
            $items_in_cart = json_decode(Mage::getModel('core/cookie')->get('es_cart_items'));
            $this->orders = new stdClass();

            // iterate through all items in the order
            foreach($order->getAllItems() as $order_item) {
                // Unserialize item data
                $item = unserialize($order_item->product_options);

                // Check whether ordered item exists in cart
                if(isset($item['simple_sku']) && isset($items_in_cart->$item['simple_sku'])) {
                    $size_attributes = array();
                    foreach(explode(',', Mage::getStoreConfig('sizeguide/sizeguide/sizeguide_size_attributes')) as $attribute) {
                        $sattr = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attribute);
                        $size_attributes[] = $sattr->getFrontendLabel();
                    }
                    
                    foreach ($item['attributes_info'] as $value) {
                        if (in_array($value['label'], $size_attributes)) {
                            $curl = curl_init("https://popup.easysize.me/collect?a=24&v=".urlencode($value['value'])."&pv={$items_in_cart->$item['simple_sku']}");
                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                            curl_exec($curl);
                            curl_close($curl);
                        }
                    }
                }
            }

            Mage::getModel('core/cookie')->delete('es_cart_items', '/');
            Mage::getModel('core/cookie')->set('es_cart_items', '', 0, '/');
        }
    }

    public function addToCart($observer) {
        $item_data = $observer->getEvent()->getQuoteItem()->getProduct()->getData();
        if(Mage::getModel('core/cookie')->get('esui') && Mage::getModel('core/cookie')->get('espageview')) {
            if(Mage::getModel('core/cookie')->get('es_cart_items') != false) {
                $items_in_cart = json_decode(Mage::getModel('core/cookie')->get('es_cart_items'));
            } else {
                $items_in_cart = new stdClass();
            }

            $items_in_cart->$item_data['sku'] = Mage::getModel('core/cookie')->get('espageview');
            Mage::getModel('core/cookie')->set('es_cart_items', json_encode($items_in_cart), 2678400, '/');
        }
    }

    public function updateCart($observer) {
        // todo. Fix update cart
    }
}