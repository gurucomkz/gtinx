<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2010
 */
LoadClass('GTMultisite');
$APP->SetPageTitle(getMessage('SITE_COMPONENT_TITLE'));
GTMultisite::__default();
//$APP->serviceDumpDatabase();
?>