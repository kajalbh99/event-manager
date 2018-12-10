<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
		</head>
		<body>
			<div class="main" style="width:700px; margin:0px auto;">
				<div class="header" style="width:100%;	float:left;	background:#ffa73b;	padding:30px 0px;">
					<img src="http://carnivalist.com/event-manager/public/img/sticky_logo.png" style="max-width:100%; margin:0px auto;display:block;" />
				</div>
				<div class="email_template" style="width: 100%;    float: left;    background: #f4f4f4;">
					<div class="email_template_content" style="    text-align:center;    padding: 20px;">
						<h2 style="text-align:center;margin-bottom:20px;">Event Query</h2>
						<p style="text-align:left;">You have received a message regarding this <b style="text-transformation:capitalize;">{{$event}}</b> event. Please check the message here.</p>
						<table style="text-align:left;">
							<tr>
								<th>Email:</th>
								<td>{{$requester_email}}</td>
							</tr>
							<tr>
								<th>Message:</th>
								<td>{{$msg}}</td>
							</tr>
						</table>
						<br/>
						<br/>Best Regards
						<br/>
						<br/>Carnivalist Team 
					</p>
				</div>
			</div>
		</div>
	</body>
</html>