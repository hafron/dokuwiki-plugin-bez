<table class="bez bez_sumarise">
	<tr>
		<th><?php echo $bezlang['id'] ?></th>
		<th><?php echo $bezlang['class'] ?></th>
		<th><?php echo $bezlang['date'] ?></th>
		<th><?php echo $bezlang['cost'] ?></th>
	</tr>
	<?php foreach ($template['tasks'] as $task): ?>
		<tr class="pr<?php echo $task['priority'] ?>">
			<td><?php echo $helper->html_task_link($task['issue'], $task['id']) ?></td>
			<td><?php echo $task['action'] ?></td>
			<td><?php echo $helper->string_time_to_now($task['date']) ?></td>
			<td>
				<?php if ($task['cost'] == ''): ?>
					<em><?php echo $bezlang['ns'] ?></em>
				<?php else: ?>
					<?php echo $task['cost'] ?>
				<?php endif ?>
			</td>
		</tr>
	<?php endforeach ?>
	<tr>
		<th><?php echo $bezlang['report_total'] ?></th>
		<td colspan="3"><?php echo count($template['tasks']) ?></td>
	</tr>
</table>
