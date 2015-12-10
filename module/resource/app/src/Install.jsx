const Install = React.createClass({

	displayName: 'Install',
	classHtmlPopupMode : 'mode-mod-resource-popup',

	// complete mount
	componentDidMount()
	{
		let self = this;
		$('html').addClass(this.classHtmlPopupMode);

		window.onhashchange = function()
		{
			self.close();
			window.onhashchange = null;
		}
	},

	// unmount component
	componentWillUnmount()
	{
		$('#' + this.props.parentID).remove();
		$('html').removeClass(this.classHtmlPopupMode);
	},

	// close popup
	close()
	{
		ReactDOM.unmountComponentAtNode(document.getElementById(this.props.parentID));
	},

	// on submit
	submit(e)
	{
		$.post(e.target.action, $(e.target).serialize(), function(data,status,xhr){
			log(data);
		});
		e.preventDefault();
	},

	render()
	{
		return (
			<article className="mod-resource-popup">
				<div className="bg" onClick={this.close}></div>
				<form action={this.props.action} method="post" onSubmit={this.submit}>
					<h1>Install</h1>
					<input type="hidden" name="install_file" defaultValue={this.props.file} />
					<fieldset>
						<legend className="blind">Install form</legend>
						<p className="guide">
							<strong>{this.props.title}</strong>은 설치경로 항목의 경로에 설치됩니다.<br/>
							경로를 변경할 수 있지만 작동이 안될 수 있습니다.
						</p>
						<dl>
							<dt><label htmlFor="frm_pwd">설치경로</label></dt>
							<dd><input type="text" name="pwd" id="frm_pwd" defaultValue={this.props.location} /></dd>
						</dl>
					</fieldset>
					<div className="loading">
						loading...
					</div>
					<nav>
						<span><button type="button" className="ui-button color-danger block" onClick={this.close}>Close</button></span>
						<span><button type="submit" className="ui-button color-install block">Install</button></span>
					</nav>
				</form>
			</article>
		);
	}
});
