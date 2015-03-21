<?php
/**
 * @package   Blockchain payment
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blockchain_payment;
use
	cs\Config,
	cs\CRUD,
	cs\Singleton;

/**
 * @method static Transactions instance($check = false)
 */
class Transactions {
	use
		CRUD,
		Singleton;

	protected $data_model = [
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
	protected $table      = '[prefix]blockchain_payment_transactions';

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
	 * @return array|bool
	 */
	function get ($id) {
		return $this->read_simple($id);
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
	 * @return array|bool|string
	 */
	function search ($search_parameters = [], $page = 1, $count = 100, $order_by = 'id', $asc = false) {
		if (!isset($this->data_model[$order_by])) {
			return false;
		}
		$where  = [];
		$params = [];
		foreach ($search_parameters as $key => $details) {
			if (isset($this->data_model[$key])) {
				$where[]  = "`$key` = '%s'";
				$params[] = $details;
			}
		}
		unset($key, $details);
		$where = $where ? 'WHERE '.implode(' AND ', $where) : '';
		if (@$search_parameters['total_count']) {
			return $this->db()->qfs(
				[
					"SELECT COUNT(`id`)
					FROM `$this->table`
					$where",
					$params
				]
			);
		} else {
			$params[] = ($page - 1) * $count;
			$params[] = $count;
			$asc      = $asc ? 'ASC' : 'DESC';
			return $this->db()->qfas(
				[
					"SELECT `id`
					FROM `$this->table`
					$where
					ORDER BY `$order_by` $asc
					LIMIT %d, %d",
					$params
				]
			);
		}
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
	 * @return bool|int Id of created transaction on success of <b>false</> on failure
	 *
	 */
	function add ($amount, $currency, $user, $module, $purpose, $description) {
		while ($secret = hash('sha512', openssl_random_pseudo_bytes(1000))) {
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
			$amount_btc	= 0.0005;
		}
		$Config              = Config::instance();
		$destination_address = $Config->module('Blockchain_payment')->bitcoin_address;
		$callback            = $Config->base_url()."/Blockchain_payment?secret=$secret";
		$blockchain_receive  = file_get_json(
			"https://blockchain.info/api/receive?method=create&address=$destination_address&callback=".urlencode($callback)
		);
		if (
			!isset($blockchain_receive['callback_url']) ||
			$blockchain_receive['callback_url'] !== $callback
		) {
			return false;
		}
		$input_address = $blockchain_receive['input_address'];
		return $this->create_simple(
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
		return (float)file_get_contents("https://blockchain.info/tobtc?currency=$currency&value=$amount");
	}
	/**
	 * Set transaction as paid (not confirmed though)
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	function set_as_paid ($id) {
		return (bool)$this->db()->q(
			"UPDATE `$this->table`
			SET `paid` = '%d'
			WHERE `id` = '%d'
			LIMIT 1",
			time(),
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
			SET `paid` = '%d'
			WHERE `id` = '%d'
			LIMIT 1",
			time(),
			$id
		);
	}
	/**
	 * Set transaction hashes
	 *
	 * @param int    $id
	 * @param string $transaction_hash
	 * @param string $input_transaction_hash
	 *
	 * @return bool
	 */
	function set_transaction_hash ($id, $transaction_hash, $input_transaction_hash) {
		return (bool)$this->db()->q(
			"UPDATE `$this->table`
			SET
				`transaction_hash`			= '%s',
				`input_transaction_hash`	= '%s'
			WHERE
				`id`				= '%d' AND
				`transaction_hash`	= ''
			LIMIT 1",
			$transaction_hash,
			$input_transaction_hash,
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
		return $this->delete_simple($id);
	}
}
