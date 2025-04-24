<?php

declare(strict_types=1);

use OCP\Util;

// style('empleados', 'semantic');  // adds css/style.(s)css
style('empleados', 'chart');
style('empleados', 'bootsdivs');
Util::addScript(OCA\Empleados\AppInfo\Application::APP_ID, 'main');
?>

<div id="content"></div>
<div id="data" data-parameters='<?php echo json_encode($_['config'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>'></div>
<div id="group-user" data-parameters='<?php echo json_encode($_['group'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>'></div>
<div id="employee" data-parameters='<?php echo json_encode($_['employee'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>'></div>
