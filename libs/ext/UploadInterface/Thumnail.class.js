/**
 * Thumnail Class
 * create, modify thumnail image
 * 
 * @Param {UploadInterface} parent : UploadInterface class
 * @Param {Object} options : option parameter
 * @Return void
*/

var Thumnail = function(parent, options) {

	var
		self = this
		,windowName = 'thumnailWindow'
		,maxImageSize = 600
		,outputSize = null
		,ratio = null
	;

	this.settings = $.extend({}, this.defaults, options);
	this.queue = null;
	this.$window = null;
	this.data = {
		srl : parent.settings.thumnail.srl
		,coords : (parent.settings.thumnail.coords) ? JSON.parse("[" + parent.settings.thumnail.coords + "]") : null
		,url : parent.settings.thumnail.url
		,location : null
	};

	/**
	 * window template
	 * 
	 * @return {Object}
	 */
	var template = function()
	{
		var str = '<div id="' + windowName + '">\n';
		str += '<div class="bg"></div>\n';
		str += '<div class="wrap">\n';
		str += '<figure></figure>\n';
		str += '<dl>\n';
		str += '<dt>출력사이즈</dt>\n';
		str += '<dd>가로:<em ar-text="width">0</em>px, 세로:<em ar-text="height">0</em>px</dd>\n';
		str += '</dl>\n';
		str += '<nav>\n';
		str += '<button type="button" rg-action="center" class="ui-button btn-highlight">중간으로</button>\n';
		str += '<button type="button" rg-action="close" class="ui-button">닫기</button>\n';
		str += '</nav>\n';
		str += '</div>\n';
		str += '</div>';
		return $(str);
	};

	/**
	 * get ratio
	 * 
	 * @param {Number} w
	 * @param {Number} h
	 * @return {Number}
	 */
	var getRatio = function(w, h)
	{
		var result = parseInt(w) / parseInt(h);
		result = Math.round(result * 1000) / 1000;
		return result;
	};

	/**
	 * get image ratio
	 * 
	 * @param {Object} $img
	 * @param {String} type
	 * @return {Number}
	 */
	var getImageRatio = function($img, type)
	{
		var size = [];
		switch(type)
		{
			case 'resize':
				size = [$img.width(), $img.height()];
				break;
			case 'resizeWidth':
				size[0] = parseInt(self.settings.size[0]);
				size[1] = parseInt(($img.height() / $img.width()) * self.settings.size[0]);
				break;
			case 'resizeHeight':
				size[0] = parseInt(($img.width() / $img.height()) * self.settings.size[1]);
				size[1] = parseInt(self.settings.size[1]);
				break;
			default:
				size = self.settings.size;
				break;
		}
		return getRatio(size[0], size[1]);
	};

	/**
	 * get output size
	 * 
	 * @param {Object} $img
	 * @param {String} type
	 * @param {Array} size
	 * @return {Array}
	 */
	var getOutputSize = function($img, type, size)
	{
		var w, h;
		switch(type)
		{
			case 'resize':
				if ($img.width() < $img.height())
				{
					w = parseInt(($img.width() / $img.height()) * size[1]);
					h = parseInt(size[1]);
				}
				else
				{
					w = parseInt(size[0]);
					h = parseInt(($img.height() / $img.width()) * size[0]);
				}
				break;
			case 'resizeWidth':
				w = parseInt(size[0]);
				h = parseInt(($img.height() / $img.width()) * size[0]);
				break;
			case 'resizeHeight':
				w = parseInt(($img.width() / $img.height()) * size[1]);
				h = parseInt(size[1]);
				break;
			default:
				w = parseInt(size[0]);
				h = parseInt(size[1]);
				break;
		}
		return [w, h];
	};

	/**
	 * resize preview
	 * 
	 * @param {Object} o
	 * @param {Number} limit
	 * @param {Number} size
	 */
	var resizePreview = function(o, limit, size)
	{
		if (o.width() > o.height())
		{
			o.width(o.width() * size);
			if (o.width() > limit)
			{
				o.width(limit);
			}
		}
		else
		{
			o.height(o.height() * size);
			if (o.height() > limit)
			{
				o.height(limit);
			}
		}
	};

	/**
	 * get select coords
	 * 
	 * @param {Object} $img
	 * @param {String} type
	 * @param {Array} coords
	 * @return {Array}
	 */
	var getSelectCoords = function($img, type, coords)
	{
		if (coords)
		{
			return coords;
		}
		else
		{
			if (type == 'crop')
			{
				return ($img.width() < $img.height()) ? [0, 0, $img.width(), 0] : [0, 0, 0, $img.height()];
			}
			else
			{
				return [0, 0, $img.width(), $img.height()];
			}
		}
	};

	/**
	 * onload preview image
	 *
	 * @return void
	 */
	var onloadPreviewImage = function()
	{
		var $img = self.$window.find('figure > img');

		// 비율값 가져오기
		ratio = getImageRatio($img, self.settings.type);

		// 아웃풋 사이즈 가져오기
		outputSize = getOutputSize($img, self.settings.type, self.settings.size);

		// 윈도우 정보에 아웃풋 사이즈 입력
		self.$window.find('[ar-text=width]').text(outputSize[0]);
		self.$window.find('[ar-text=height]').text(outputSize[1]);

		// 프리뷰 이미지 적정크기로 줄이기
		resizePreview($img, maxImageSize, 0.7);

		// 좌표값 가져오기
		self.data.coords = getSelectCoords($img, self.settings.type, self.data.coords);

		// init Jcrop
		$img.Jcrop({
			bgOpacity : .4
			,setSelect : self.data.coords
			,aspectRatio : ratio
			,minSize : [50, 50]
			,onSelect : function(e) {
				self.data.coords = [
					parseInt(e.x)
					,parseInt(e.y) > 0 ? parseInt(e.y) : 0
					,parseInt(e.x2)
					,parseInt(e.y2)
					,parseInt(e.w)
					,parseInt(e.h)
				];
			}
		}, function(){
			var jcrop = this;
			var $btnCenter = self.$window.find('button[rg-action=center]');
			var $btnClose = self.$window.find('button[rg-action=close], div.bg');
			var $wrap = self.$window.find('div.wrap');
	
			$('body').addClass('thumnailWindowMode');
			$wrap.css({
				marginLeft : 0 - ($wrap.width() * 0.5) + 'px'
				/* ,marginTop : 0 - ($wrap.height() * 0.5) + 'px' */
			});
	
			$btnCenter.on('click', function(){
				var x, y;
				var src = jcrop.tellSelect();
				x = ($img.width() * 0.5) - (src.w * 0.5);
				y = ($img.height() * 0.5) - (src.h * 0.5);
				self.data.coords = [
					parseInt(x)
					,parseInt(y) > 0 ? parseInt(y) : 0
					,parseInt(x + src.w)
					,parseInt(y + src.h)
					,parseInt(src.w)
					,parseInt(src.h)
				];
				jcrop.animateTo(self.data.coords);
			});
			$btnClose.on('click', function(){
				self.close();
			});
		});
	};

	/**
	 * get image data
	 * 
	 * @param {String} img
	 * @return {String}
	 */
	var getImageData = function(img)
	{
		var
			$canvas = $('<canvas width="' + outputSize[0] + '" height="' + outputSize[1] + '" />')
			,context = $canvas.get(0).getContext('2d')
			,$img = self.$window.find('figure > img')
			,realSize = [$img.get(0).naturalWidth, $img.get(0).naturalHeight]
			,coords = self.data.coords
			,ratio = [realSize[0] / $img.width(), realSize[1] / $img.height()]
		;

		// draw background
		context.fillStyle = '#ffffff';
		context.fillRect(0, 0, outputSize[0], outputSize[1]);

		// draw photo
		context.drawImage(
			$img.get(0)
			,coords[0] * ratio[0] // x
			,coords[1] * ratio[1] // y
			,coords[4] * ratio[0] // x2
			,coords[5] * ratio[1] // y2
			,0 // dx
			,0 // dy
			,outputSize[0] // dw
			,outputSize[1] // dh
		);

		return $canvas.get(0).toDataURL("image/jpeg", self.settings.quality);
	};


	/**
	 * open
	 * 
	 * @param {Object} item
	 * @return void
	 */
	this.open = function(item)
	{
		if (!self.$window)
		{
			self.queue = item;
			self.$window = template();

			if (item.srl !== self.data.srl)
			{
				self.data.coords = null;
			}

			self.data.srl = item.srl;
			self.data.location = parent.settings.fileDir + item.location;

			var $figure = self.$window.find('figure');
			var $img = $('<img src="' + self.data.location + '" />');
			$figure.append($img);
			$('body').append(self.$window);
			$img.get(0).onload = onloadPreviewImage;
		}
	};

	/**
	 * close
	 *
	 * @return void
	 */
	this.close = function()
	{
		var thumnail_srl = parent.settings.thumnail.srl;
		var thumnail_coords = parent.settings.thumnail.coords;

		if (self.data.srl !== thumnail_srl || self.data.coords.toString() !== thumnail_coords)
		{
			self.data.image = getImageData(self.data.location);
			parent.queue.updateThumnailClass(self.queue.element);
		}

		$('body').removeClass('thumnailWindowMode');
		self.$window.remove();
		self.$window = null;
		self.queue = null;
	}

};


// default variables
Thumnail.prototype.defaults = {
	type : 'crop'
	,size : [200,200]
	,quality : 0.7
};