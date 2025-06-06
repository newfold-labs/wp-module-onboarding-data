<?php

namespace NewfoldLabs\WP\Module\Data\EventQueue;

trait Queryable {

	/**
	 * Get a new query instance
	 *
	 * @return \WP_Forge\QueryBuilder\Query
	 */
	protected function query() {
		return $this->container->get( 'query' );
	}

	/**
	 * Get the table name
	 *
	 * @return string
	 */
	protected function table() {
		return $this->container->get( 'table' );
	}

	/**
	 * Bulk inserts records into a table using WPDB.  All rows must contain the same keys.
	 * Returns number of affected (inserted) rows.
	 *
	 * @param  string          $table
	 * @param  non-empty-array $rows
	 *
	 * @return bool|int
	 */
	protected function bulkInsert( string $table, array $rows ) {
		global $wpdb;

		// Extract column list from first row of data
		$columns = array_keys( $rows[0] );
		asort( $columns );
		$columnList = '`' . implode( '`, `', $columns ) . '`';

		// Start building SQL, initialise data and placeholder arrays
		$sql          = "INSERT INTO `$table` ($columnList) VALUES\n";
		$placeholders = array();
		$data         = array();

		// Build placeholders for each row, and add values to data array
		foreach ( $rows as $row ) {
			ksort( $row );
			$rowPlaceholders = array();

			foreach ( $row as $key => $value ) {
				$data[]            = $value;
				$rowPlaceholders[] = is_numeric( $value ) ? '%d' : '%s';
			}

			$placeholders[] = '(' . implode( ', ', $rowPlaceholders ) . ')';
		}

		// Stitch all rows together
		$sql .= implode( ",\n", $placeholders );

		// Run the query.  Returns number of affected rows.
		return $wpdb->query( $wpdb->prepare( $sql, $data ) );
	}
}
