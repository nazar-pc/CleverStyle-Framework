<?php
/**
 * @package   Blockchain payment
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blockchain_payment;
use
	cs\Config,
	cs\CRUD_helpers,
	cs\Singleton;

/**
 * @method static Transactions instance($check = false)
 */
class Transactions {
	use CRUD_helpers {
		search as crud_search;
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
	function get ($id) {
		return $this->read($id);
	}
	/**
	 * Transactions search
	 *
	 * @param mixed[] $search_parameters Array in form [attribute => value];
	 *                                   if `total_count => 1` element is present - total number of found rows will be returned instead of rows themselves
	 * @param int     $page
	 * @param int     $count
	 * @param string  $order_by
	 * @param bool    $asc
	 *
	 * @return array|false|int
	 */
	function search ($search_parameters = [], $page = 1, $count = 100, $order_by = 'id', $asc = false) {
		return $this->crud_search($search_parameters, $page, $count, $order_by, $asc);
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
	function add ($amount, $currency, $user, $module, $purpose, $description) {
		while ($secret = hash('sha512', random_bytes(1000))) {
			if ($this->db_prime()->qf(
				"SELECT `id`
				FROM `$this->table`
				WHERE `id` = '$secret'
				LIMIT 1"
			)
			) {
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
			[
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
			]
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
	function convert_to_btc ($amount, $currency) {
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
	function set_as_paid ($id, $transaction_hash, $input_transaction_hash) {
		return (bool)$this->db()->q(
			"UPDATE `$this->table`
			SET
				`paid`						= '%d',
				`transaction_hash`			= '%s',
				`input_transaction_hash`	= '%s'
			WHERE
				`id`	= '%d' AND
				`paid`	= '0'
			LIMIT 1",
			time(),
			$transaction_hash,
			$input_transaction_hash,
			$id
		);
	}
	/**
	 * Set transaction as confirmed (each paid transaction should be confirmed in order to be completed)
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	function set_as_confirmed ($id) {
		return (bool)$this->db()->q(
			"UPDATE `$this->table`
			SET `confirmed` = '%d'
			WHERE
				`id`		= '%d' AND
				`confirmed`	= '0'
			LIMIT 1",
			time(),
			$id
		);
	}
	/**
	 * Delete specified order
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	function del ($id) {
		return $this->delete($id);
	}
}
