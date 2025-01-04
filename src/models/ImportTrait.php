<?php
/**
 * @author: Sergey Mashkov (serge@asse.com)
 * Date: 6/18/23 12:01 PM
 * Project: asse-db-template
 */

namespace xpbl4\import\models;

trait ImportTrait
{
	/**
	 * @inheritdoc
	 */
	public static function import($reader, $row, $data)
	{
		if ($row == 0) return true;

		$_primaryKey = [];
		$_data = [];
		foreach ($reader->headers AS $_id => $_header) {
			$_attribute = $_header['attribute'];
			if (!empty($_attribute) && $_attribute != 'none') {
				if (empty($_data[$_attribute])) $_data[$_attribute] = trim($data[$_id]);
				else $_data[$_attribute] .= ' ' . trim($data[$_id]);
				if ($_header['key']) $_primaryKey[$_attribute] = trim($data[$_id]);
			}
		}

		// Create model from data
		$model = null;
		if (!empty($_primaryKey)) $model = static::findOne($_primaryKey);
		if ($model == null) $model = new static();

		try {
			$model->setImportAttributes($_data);

			if (!$model->hasErrors()) {
				if (!$model->save()) {
					$reader->addError($row, 'Save: '.implode(', ', $model->getFirstErrors()));

					return false;
				}
			} else {
				$reader->addError($row, 'Model: '.implode(', ', $model->getFirstErrors()).'<!-- pre>'.print_r($_data, true).'</pre -->');

				return false;
			}
		} catch (\Exception $e) {
			$reader->addError($row, 'Exception: '.$e->getMessage());

			return false;
		}

		return $model;
	}

	/**
	 * Sets the imported attribute values in a massive way.
	 * @param array $values attribute values (name => value) to be assigned to the model.
	 * @param bool $safeOnly whether the assignments should only be done to the safe attributes.
	 * A safe attribute is one that is associated with a validation rule in the current [[scenario]].
	 * @see safeAttributes()
	 * @see attributes()
	 */
	public function setImportAttributes($attributes, $safeOnly = true)
	{
		$this->setAttributes($attributes, $safeOnly);
		return $this->hasErrors();
	}
}