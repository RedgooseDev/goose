var FilesQueue = function(getParent, $el) {

	var self = this;
	var parent = getParent;

	this.$preview = $el.children('figure.thumnail');
	this.$index = $el.children('ul');
	this.index = {};
	this.count = 0;
	this.active = [];


	/**
	 * template
	 *
	 * @author : redgoose
	 * @param {String} key
	 * @param {String} filename
	 * 	@param {String} state
	 * @param {String} type
	 * @return {Object} : queue element
	 */
	this.template = function(key, filename, state, type)
	{
		var item = '<li key="' + key + '">\n';
		item += '\t<div class="body">\n';
		item += '\t\t<span class="name">' + filename + '</span>\n';
		item += (state == 'ready') ? '\t\t<span class="size">0%</span>\n' : '';
		item += '\t\t<span class="state">' + state + '</span>\n';
		item += '\t</div>\n';
		if (state == 'ready')
		{
			item += '\t<div class="progress">\n';
			item += '\t\t<p class="graph"><span></span></p>\n';
			item += '\t</div>\n';
		}
		item += '\t<nav>\n';
		item += (/^image/i.test(type)) ? '\t\t<button type="button" rg-action="thumnail" class="icn-thumnail" title="썸네일이미지"></button>\n' : '';
		item += '\t\t<button type="button" rg-action="insertContent" class="icn-plus" title="본문에 삽입하기"></button>\n';
		item += '\t\t<button type="button" rg-action="delete" class="icn-close" title="삭제"></button>\n';
		item += '\t</nav>\n';
		item += '</li>';

		return $(item);
	};

	/**
	 * queue event init
	 *
	 * @param {Object} obj
	 * @return void
	 */
	var queueEventInit = function(obj)
	{
		// select queue
		obj.on('click', function(){
			var item = self.getIndexItem($(this).attr('key'));
			self.selectQueue(item);
		});

		// delete queue
		obj.find('nav > button').on('click', function(e){
			e.stopPropagation();
			var $queue = $(this).closest('li');
			switch($(this).attr('rg-action'))
			{
				case 'delete':
					if (confirm('파일을 삭제하시겠습니까?'))
					{
						self.removeQueue($queue);
					}
					break;
				case 'thumnail':
					var queue = parent.queue.index[$queue.attr('key')];
					if (/^image/i.test(queue.filetype))
					{
						parent.thumnail.open(queue);
					}
					break;
				case 'insertContent':
					parent.insertContent($queue);
					break;
			}
		});
	};


	/**
	 * create queue
	 *
	 * @author : redgoose
	 * @param {Object} file
	 * @return {String}
	 */
	this.createQueue = function(file)
	{
		var idx = self.count;
		var key = 'queue-' + idx;
		var state = (file.state) ? file.state : 'ready';
		var $dom = self.template(key, file.name, state, file.type);

		self.index[key] = {
			filename : file.name
			,filesize : file.size
			,filetype : file.type
			,state : state
			,element : $dom
			,location : file.loc
			,type : file.type2
			,srl : file.srl
		};

		// insert queue
		$el.children('ul').append($dom);

		// event init
		queueEventInit($dom);

		self.count++;
		return key;
	};

	/**
	 * select queue
	 *
	 * @author : redgoose
	 * @param {Object} queue
	 * @return void
	 */
	this.selectQueue = function(queue)
	{
		if (queue.element.hasClass('on'))
		{
			queue.element.removeClass('on');
			self.clearPreview();
		}
		else
		{
			if (!parent.key)
			{
				self.$index.children().removeClass('on');
			}
			queue.element.addClass('on');

			if (/^image/i.test(queue.filetype) && self.$preview.length)
			{
				self.$preview.html('<img src="' + parent.settings.fileDir + queue.location + '" alt="" />');
			}
		}
	};

	/**
	 * select all queue
	 *
	 * @return void
	 */
	this.selectAllQueue = function()
	{
		if (self.$index.children('li').hasClass('on'))
		{
			self.$index.children('li').removeClass('on');
		}
		else
		{
			self.$index.children('li').addClass('on');
		}
	};

	/**
	 * remove queue
	 *
	 * @author : redgoose
	 * @param {Object} $queue
	 * @return void
	 */
	this.removeQueue = function($queue)
	{
		var action = parent.settings.removeAction;
		var srls = [];
		$queue.each(function(){
			var item = self.getIndexItem($(this).attr('key'));
			srls.push({
				table : (item.type == 'session') ? 'file_tmp' : 'file',
				srl : parseInt(item.srl)
			});
		});

		if (action)
		{
			var ajax = $.ajax({
				url : action
				,type : 'post'
				,dataType : 'json'
				,data : {
					data : JSON.stringify(srls)
				}
			});

			ajax
				.fail(function(o){
					log('AJAX ERROR');
					if (o.error)
					{
						o.error(o.stateText);
					}
				})
				.done(function(o){
					if (o.state == 'success')
					{
						$queue.each(function(){
							delete self.index[$(this).attr('key')];
						});
						$queue.fadeOut(400, function(){
							$(this).remove();
						});
						parent.refreshAddQueue();
					}
				})
			;
		}
	};

	/**
	 * clear preview
	 * 프리뷰의 내용을 삭제한다.
	 *
	 * @return void
	 */
	this.clearPreview = function()
	{
		self.$preview.html('');
	};

	/**
	 * get index item
	 *
	 * @param {String} key
	 * @return {Object}
	 */
	this.getIndexItem = function(key)
	{
		return self.index[key];
	};

	/**
	 * get items
	 *
	 * @return {Array}
	 */
	this.getItems = function()
	{
		return self.$index.children('li.on').map(function(o){
			return self.index[$(this).attr('key')];
		});
	};

	/**
	 * get thumnail item
	 *
	 * @return {Array}
	 */
	this.getThumnailItem = function()
	{
		return self.index[self.$index.children('li.thumnail').attr('key')];
	};

	/**
	 * remove thumnail class
	 */
	this.updateThumnailClass = function($item)
	{
		self.$index.children('li.thumnail').removeClass('thumnail');
		$item.addClass('thumnail');
	}

};