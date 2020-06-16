<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;
defined('MIB_MANAGE') or exit;

// Requette Ajax
defined('MIB_AJAX') or exit;
define('MIB_AJAXED', 1);

?>
<div class="alert">
	Bienvenue sur le Back Office.

</div>
<?php

/*
?>
<table class="dashboard">
	<tbody>
		<tr>
			<td class="db_drop">
				<div class="db_drag bdbox">
					<div class="db_title grad bdbox">Drag #1</div>
					<div class="db_body">Drag #1 Content Area</div>
				</div>
				<div class="db_drag bdbox">
					<div class="db_title grad bdbox">Drag #4</div>
					<div class="db_body">Drag #4 Content Area</div>
				</div>
				<div class="db_drag bdbox">
					<div class="db_title grad bdbox">Drag #7</div>
					<div class="db_body">Drag #7 Content Area</div>
				</div>
			</td>
			<td class="db_drop">
				<div class="db_drag bdbox">
					<div class="db_title grad bdbox">Drag #2</div>
					<div class="db_body">Drag #2 Content Area</div>
				</div>
				<div class="db_drag bdbox">
					<div class="db_title grad bdbox">Drag #5</div>
					<div class="db_body">Drag #5 Content Area</div>
				</div>
				<div class="db_drag bdbox">
					<div class="db_title grad bdbox">Drag #8</div>
					<div class="db_body">Drag #8 Content Area</div>
				</div>
			</td>
			<td class="db_drop">
				<div class="db_drag bdbox">
					<div class="db_title grad bdbox">Drag #3</div>
					<div class="db_body">Drag #3 Content Area</div>
				</div>
				<div class="db_drag bdbox">
					<div class="db_title grad bdbox">Drag #6</div>
					<div class="db_body">Drag #6 Content Area</div>
				</div>
				<div class="db_drag bdbox">
					<div class="db_title grad bdbox">Drag #9</div>
					<div class="db_body">Drag #9 Content Area</div>
				</div>
			</td>
		</tr>
	</tbody>
</table>
<script type="text/javascript">
	marker = new Element('div', {'class': 'dashboard_marker'}).setStyles({'opacity': 0.7}).inject(document.getElement('body'));
	$$('div.db_drag').each(function(d){
		d.makeDraggable({
			droppables: $$('td.db_drop'), 
			handle: d.getElement('div.db_title'), 
			onBeforeStart: function(){
				marker.setStyles({'display': 'block', 'height': d.getCoordinates().height, 'width': d.getCoordinates().width}).inject(d, 'after');
				d.setStyles({'position': 'absolute', 'top': (d.getCoordinates().top - d.getStyle('margin-top').toFloat()), 'left': (d.getCoordinates().left - d.getStyle('margin-left').toFloat()), 'width': d.getCoordinates().width, 'opacity': 0.7, 'z-index': 3});
				if (window.console) console.log('Before Start');
			}, 
			onStart: function(){
				if (window.console) console.log('Start');
			}, 
			onEnter: function(el, drop){
				drop.adopt(marker.setStyles({'display': 'block', 'height': el.getCoordinates().height, 'width': el.getCoordinates().width}));
				if (window.console) console.log('Entering');
			}, 
			onLeave: function(el, drop){
				marker.dispose();
				if (window.console) console.log('Leaving');
			}, 
			onDrag: function(el){
				target = null;
				drop = marker.getParent();
				if (drop && drop.getChildren().length > 1){
					kids = drop.getChildren();
					mouseY = this.mouse.now.y;
					kids.each(function(k){
						if (mouseY > (k.getCoordinates().top + Math.round(k.getCoordinates().height / 2))) target = k;
					});
					if (target == null){
						if (kids[0] != marker) marker.inject(drop, 'top');
						if (window.console) console.log('TOP');
					} else {
						if ((target != marker) && (target != marker.getPrevious())) marker.inject(target, 'after');
						if (window.console) console.log('AFTER');
					}
				}
				if (window.console) console.log('Dragging');
			}, 
			onDrop: function(el, drop){
				if (drop) el.setStyles({'position': 'relative', 'top': '0', 'left': '0', 'width': null, 'opacity': 1, 'z-index': 1}).replaces(marker);
				else el.setStyles({'position': 'relative', 'top': '0', 'left': '0', 'opacity': 1, 'z-index': 1});
				if (window.console) console.log('Dropping');
			}, 
			onComplete: function(el){
				if (window.console) console.log('Done');
			}, 
			onCancel: function(el){
				marker.dispose();
				el.setStyles({'position': 'relative', 'top': '0', 'left': '0', 'width': null, 'opacity': 1, 'z-index': 1});
				if (window.console) console.log('Cancel');
			}
		});
	});
</script>
<?php
*/