function DivSwitch(obj,newdiv){
	if(document.getElementById){
		var el = $('#'+obj).first();
		var ar = $('#'+newdiv+' > div');
		var def = $('.defaultDiv').first();
		if (el.css('display') == 'none'){
			for (var i=0; i<ar.length; i++){
				$(ar[i]).css('display', 'none');
			}
			el.css('display', 'block');
		}else{
			el.css('display', '');
			def.css('display', 'block');
		}
	}
}

function hideBG(offDiv, offBG, onDiv, onBG){
	if(document.getElementById){
		var elOff = $('#'+offDiv).first();
		var elOn = $('#'+onDiv).first();
		elOff.css('background', 'transparent');
		elOn.css('background', onBG);
	}
}
