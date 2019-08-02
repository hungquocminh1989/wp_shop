<?php defined( 'ABSPATH' ) || exit; ?>

<style type="text/css">
@keyframes lds-ring {
  0% {
    transform: rotate(0)
  }
  100% {
    transform: rotate(360deg)
  }
}
.lds-ring > div{
  position: absolute;
  top: 25%;
  left: 25%;
  width: 50%;
  height: 50%;
  border-radius: 50%;
  border: 5px solid #9edafe;
  border-color: #9edafe transparent transparent transparent;
  animation: lds-ring 1.5s cubic-bezier(0.5,0,0.5,1) infinite;
}
.lds-ring > div:nth-child(2) {
  animation-delay: .195s;
}
.lds-ring > div:nth-child(3) {
  animation-delay: .39s;
}
.lds-ring > div:nth-child(4) {
  animation-delay: .585s;
}
.loading_screen
{   
    height: 100%;
    width: 100%;
    position: fixed;
    z-index: 99999999999999;
    left: 0;
    top: 0;
    background-color: rgb(220,220,220);
    background-color: rgba(220,220,220, 0.8);
    overflow-x: hidden;
    overflow-y: hidden;
    display:none;
}
.loading_dialog{
	min-width: 10px;
	min-height: 10px;
	max-width: 100px;
	max-height: 100px;
	/*background-color: blue;*/
	position: absolute;
	top:0;
	bottom: 0;
	left: 0;
	right: 0;
	margin: auto;
}
</style>

<div id="loading_screen" class="loading_screen">
    <div class="loading_dialog center">
        <!--Preloader-->
        <div class="lds-css">
            <div class="lds-ring" style="width:100%;height:100%">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>
    </div>
</div>