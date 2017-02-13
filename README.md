# Backbone-Modal-View
[![License](https://img.shields.io/badge/License-GPL v3-blue.svg)](http://www.gnu.org/licenses/gpl-3.0)
![Downloads](https://img.shields.io/packagist/dt/wpbp/backbone-modal-view.svg) 

Add on a button a Backbone Modal View with a list

#What?

Do you want a modal with search included withoout develop in Javascript?  
This code is very simple, is generated a button that you can print or return where you prefer that will open a modal view.  
You have only to define the output of the list (HTML) and the AJAX call where save the element checked.  
Check the code example and try it to understand how can save so much your precious time!

The example code in the class, save the checked element as a string separated by ', ' in an user field.

## Example

![Screenshot](https://cloud.githubusercontent.com/assets/403283/22898704/983785c0-f228-11e6-8550-0a746a4b7ad0.gif)

```php
new BB_Modal_View( array(
			'id' => 'test', // ID of the modal view
			'hook' => 'admin_notices', // Where return or print the button
			'input' => 'checkbox', // Or radio
			'label' => __( 'Open Modal' ), // Button text
			'data' => array( 'rand' => rand() ), // Array of custom datas
			'ajax' => array( $this, 'ajax_posts' ), // Ajax function for the list to show on the modal
			'ajax_on_select' => array( $this, 'ajax_posts_selected' ), // Ajax function to execute on Select button
			'echo_button' => true // Do you want echo the button in the hook chosen or only return?
		));

```
