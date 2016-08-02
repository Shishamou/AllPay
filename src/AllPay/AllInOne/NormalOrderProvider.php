<?php
/**
 * NormalOrderProvider
 *
 * @author Shisha <shisha225@gmail.com>
 */

namespace AllPay\AllInOne;

class NormalOrderProvider extends OrderProvider
{
	public function createOrder($tradeNo)
	{
		$oPayment = parent::{__FUNCTION__}($tradeNo);

		$oPayment->Send['ChoosePayment'] = \PaymentMethod::ALL;
		$oPayment->Send['ChooseSubPayment'] = \PaymentMethodItem::None;
		$oPayment->Send['NeedExtraPaidInfo'] = \ExtraPaymentInfo::No;
		$oPayment->Send['DeviceSource'] = \DeviceType::PC;
		$oPayment->Send['IgnorePayment'] = "<<您不要顯示的付款方式>>";

		return $oPayment;
	}
}
