<?php
/**
 * @category   PSystem
 * @package    PSystem_AjaxQuickCart
 * @author     Pascal System <info@pascalsystem.pl>
 * @version    1.0.2
 */

/**
 * @see Mage_Checkout_CartController
 */
require_once 'Mage/Checkout/controllers/CartController.php';

/**
 * Pascal Quick Cart Ajax Header JavaScripts
 * 
 * @category   PSystem
 * @package    PSystem_AjaxQuickCart
 * @author     Pascal System <info@pascalsystem.pl>
 * @version    1.0.2
 */
class PSystem_AjaxQuickCart_ViewcartController extends Mage_Checkout_CartController {
    /**
     * Index action display only products in cart
     * 
     * @return void
     */
    public function indexAction() {
        $this->loadLayout()
            ->_initLayoutMessages('checkout/session')
            ->_initLayoutMessages('catalog/session');
        $this->renderLayout();
    }
    
    /**
     * Refresh box with items in cart
     * 
     * @return void
     */
    public function refreshAction() {
        /* @var $layout Mage_Core_Model_Layout */
        $layout = $this->loadLayout()->getLayout();
        /* @var $ajaxBlock PSystem_AjaxQuickCart_Block_Refresh_Response */
        if (($ajaxBlock = $layout->getBlock('ajax.response'))
            && ($ajaxBlock instanceof PSystem_AjaxQuickCart_Block_Refresh_Response)) {
            echo $ajaxBlock->toHtml();
            exit;
        }
    }

    /**
     * Update cart item quantity via AJAX
     * 
     * @return void
     */
    public function updatePostAction() {
        $cart = Mage::getSingleton('checkout/cart');
        $cartData = $this->getRequest()->getPost('cart');

        $response = [];
        $grandTotal = 0; // Initialize grand total

        foreach ($cartData as $itemId => $data) {
            $qty = (int)$data['qty'];
            if ($qty > 0) {
                $cart->updateItem($itemId, $qty);
            } else {
                $cart->removeItem($itemId);
            }
        }

        // Loop through each item in the cart to calculate totals
        foreach ($cart->getQuote()->getAllItems() as $_item) {
            $itemPrice = $_item->getProduct()->getPrice(); // Get item price
            $itemQty = $_item->getQty(); // Get item quantity
            $grandTotal += $itemPrice * $itemQty; // Calculate total for this item
            $itemSubtotal = Mage::helper('checkout')->formatPrice($itemPrice * $itemQty);
            $response['itemSubtotals'][$_item->getId()] = $itemSubtotal; // Store subtotal for each item
        }

        // Format the grand total
        $response['newTotal'] = Mage::helper('checkout')->formatPrice($grandTotal);
        $response['success'] = true;

        // Send the response back to the JavaScript
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }
}
