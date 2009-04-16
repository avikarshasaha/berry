/*
This flash player created by Martin Lain

http://www.1pixelout.net/code/audio-player-wordpress-plugin/

License:

    Copyright 2005-2006  Martin Laine  (email : martin@1pixelout.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

var AudioPlayer = function () {
	var instances = [];
	var activePlayerID;
	var playerURL = "";
	var defaultOptions = {};
	var currentVolume = -1;

	function getPlayer(playerID) {
		return document.all ? window[playerID] : document[playerID];
	}

	return {
		setup: function (url, options) {
	        playerURL = url;
	        defaultOptions = options;
	    },

		getPlayer: function (playerID) {
			return getPlayer(playerID);
		},

	    embed: function (elementID, options) {
			var instanceOptions = {};
	        var key;
	        var so;
			var bgcolor;
			var wmode;

			var flashParams = {};
			var flashVars = {};
			var flashAttributes = {};

	        // Merge default options and instance options
			for (key in defaultOptions) {
	            instanceOptions[key] = defaultOptions[key];
	        }
	        for (key in options) {
	            instanceOptions[key] = options[key];
	        }

			if (instanceOptions.transparentpagebg == "yes") {
				flashParams.bgcolor = "#FFFFFF";
				flashParams.wmode = "transparent";
			} else {
				if (instanceOptions.pagebg) {
					flashParams.bgcolor = "#" + instanceOptions.pagebg;
				}
				flashParams.wmode = "opaque";
			}

			flashParams.menu = "false";

	        for (key in instanceOptions) {
				if (key == "pagebg" || key == "width" || key == "transparentpagebg") {
					continue;
				}
	            flashVars[key] = instanceOptions[key];
	        }

			flashAttributes.name = elementID;
			flashAttributes.style = "outline: none";

			flashVars.playerID = elementID;

			swfobject.embedSWF(playerURL, elementID, instanceOptions.width.toString(), "24", "9", false, flashVars, flashParams, flashAttributes);


			instances.push(elementID);
	    },

		syncVolumes: function (playerID, volume) {
			currentVolume = volume;
			for (var i = 0; i < instances.length; i++) {
				if (instances[i] != playerID) {
					getPlayer(instances[i]).setVolume(currentVolume);
				}
			}
		},

		activate: function (playerID) {
			if (activePlayerID && activePlayerID != playerID) {
				getPlayer(activePlayerID).close();
			}

			activePlayerID = playerID;
		},

		load: function (playerID, soundFile, titles, artists) {
			getPlayer(playerID).load(soundFile, titles, artists);
		},

		close: function (playerID) {
			getPlayer(playerID).close();
			if (playerID == activePlayerID) {
				activePlayerID = null;
			}
		},

		open: function (playerID) {
			getPlayer(playerID).open();
		},

		getVolume: function (playerID) {
			return currentVolume;
		}

	}

}();
