# yii2-import

Extension for the Yii2 framework for importing data with [PhpSpreadsheet].

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

    php composer.phar require --prefer-dist xpbl4/yii2-import "*"

or add

    "xpbl4/yii2-import": "*"

to the require section of your `composer.json` file.

## Usage

Implement the `ImportInterface`:

    class Contact extends \yii\db\ActiveRecord implements ImportInterface
    {
        ...

        public function import($reader, $row, $data)
    	{
    	    if ($row == 0) {
    	        if ($data != ['First Name', 'Last Name', 'Email']) {
    	            $reader->addError($row, 'Invalid headers.');
    	            return false;
    	        }
    	        // Skip header row
    	        return true;
    	    }

    	    // Create contact from data
    	    $contact = new Contact;
    	    ...
        }

    }

Now you can import data using `ExcelReader` and your class as the `model`:

    public function actionImport()
    {
        $form = new ImportForm();
        $form->file = UploadedFile::getInstanceByName('file');

        if ($form->validate()) {
            Contact::deleteAll();
            $reader = new ExcelReader(['model' => Contact::className()]);
            $reader->import($form->file->tempName);
            if ($reader->getErrors()) {
                // Handle errors
                ...
            }
        }
        return $form;
    }

[PhpSpreadsheet]: https://github.com/PHPOffice/PhpSpreadsheet