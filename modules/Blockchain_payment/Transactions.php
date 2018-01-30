<?php
/**
 * @package  Blockchain payment
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs\modules\Blockchain_payment;
use
	cs\Config,
	cs\CRUD_helpers,
	cs\Singleton;

/**
 * @method static $this instance($check = false)
 */
class Transactions {
	use CRUD_helpers {
		search as public;
	}
	use
		Singleton;

	protected $data_model     = [
		'id'                     => 'int',
		'amount'                 => 'float:0',
		'currency'               => 'text',
		'user'                   => 'int:0',
		'module'                 => 'text',
		'purpose'                => 'text',
		'description'            => 'text',
		'amount_btc'             => 'float:0.0005',
		'destination_address'    => 'text',
		'input_address'          => 'text',
		'created'                => 'int:0',
		'paid'                   => 'int:0',
		'confirmed'              => 'int:0',
		'secret'                 => 'text',
		'transaction_hash'       => 'text',
		'input_transaction_hash' => 'text'
	];
	protected $table          = '[prefix]blockchain_payment_transactions';
	protected $blockchain_url = 'https://blockchain.info';

	/**
	 * Returns database index
	 *
	 * @return int
	 */
	protected function cdb () {
		return Config::instance()->module('Blockchain_payment')->db('transactions');
	}
	/**
	 * Get transaction
	 *
	 * @param int|int[] $id
	 *
	 * @return array|false
	 */
	public function get ($id) {
		return $this->read($id);
	}
	/**
	 * Add new transaction
	 *
	 * @param float  $amount
	 * @param string $currency
	 * @param int    $user
	 * @param string $module
	 * @param string $purpose
	 * @param string $description
	 *
	 * @return false|int Id of created transaction on success of <b>false</> on failure
	 *
	 */
	public function add ($amount, $currency, $user, $module, $purpose, $description) {
		while ($secret = hash('sha512', random_bytes(1000))) {
			if ($this->search(['secret' => $secret, 'total_count' => true])) {
				break;
			}
		}
		$amount_btc = $currency == 'BTC' ? $amount : $this->convert_to_btc($amount, $currency);
		// Minimal acceptable payment
		if ($amount_btc < 0.0005) {
			$amount_btc = 0.0005;
		}
		$Config              = Config::instance();
		$destination_address = $Config->module('Blockchain_payment')->bitcoin_address;
		$callback            = $Config->base_url()."/Blockchain_payment?secret=$secret";
		$blockchain_receive  = file_get_json(
			"$this->blockchain_url/api/receive?method=create&address=$destination_address&callback=".urlencode($callback)
		);
		if (
			!isset($blockchain_receive['callback_url']) ||
			$blockchain_receive['callback_url'] !== $callback
		) {
			return false;
		}
		$input_address = $blockchain_receive['input_address'];
		return $this->create(
			$amount,
			$currency,
			$user,
			$module,
			$purpose,
			$description,
			$amount_btc,
			$destination_address,
			$input_address,
			time(),
			0,
			0,
			$secret,
			'',
			''
		);
	}
	/**
	 * Convert any supported currency to BTC (external API call)
	 *
	 * @param float  $amount
	 * @param string $currency
	 *
	 * @return float
	 */
	public function convert_to_btc ($amount, $currency) {
		return (float)file_get_contents("$this->blockchain_url/tobtc?currency=$currency&value=$amount");
	}
	/**
	 * Set transaction as paid (not confirmed though) and set transaction hashed
	 *
	 * @param int    $id
	 * @param string $transaction_hash
	 * @param string $input_transaction_hash
	 *
	 * @return bool
	 */
	public function set_as_paid ($id, $transaction_hash, $input_transaction_hash) {
		$data                           = $this->get($id);
		$data['paid']                   = time();
		$data['transaction_hash']       = $transaction_hash;
		$data['input_transaction_hash'] = $input_transaction_hash;
		return $this->update($data);
	}
	/**
	 * Set transaction as confirmed (each paid transaction should be confirmed in order to be completed)
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function set_as_confirmed ($id) {
		$data              = $this->get($id);
		$data['confirmed'] = time();
		return $this->update($data);
	}
	/**
	 * Delete specified order
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function del ($id) {
		return $this->delete($id);
	}
}
