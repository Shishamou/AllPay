<?php
/**
 * FeedbackProvider
 *
 * @author Shisha <shisha225@gmail.com>
 */

namespace AllPay\AllInOne;

class FeedbackProvider extends AllInOneProvider
{
	public function checkOutFeedback()
	{
		return $this->sdk->checkOutFeedback();
	}
}
