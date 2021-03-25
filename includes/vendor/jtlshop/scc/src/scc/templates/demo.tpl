<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Smarty component collection demo</title>
	<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,shrink-to-fit=no">
	<meta name="msapplication-starturl" content="/">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
</head>
<body>
<style>
	.bv-example-row .row + .row {
		margin-top: 1rem;
	}

	.bv-example-row .row > .col:not(.header),
	.bv-example-row .row > [class^=col-] {
		padding-top: .75rem;
		padding-bottom: .75rem;
		background-color: rgba(86, 61, 124, .15);
		border: 1px solid rgba(86, 61, 124, .2);
	}

	.bv-example-row-flex-cols .row {
		min-height: 10rem;
		background-color: rgba(255, 0, 0, .1);
	}
</style>
<div id="app" class="container">

	<h2 class="mt-4">Collapse</h2>

	{button href="#test-collapse" data=["toggle"=>"collapse"] role="button" aria=["expanded"=>"true","controls"=>"test-collapse"] title="btn-title!"}
		Toggle 1
	{/button}
	{collapse id="test-collapse" class="mt-2" title="collapse title!"}
		{card}Hallo Welt!{/card}
	{/collapse}
	<hr>
	{button href="#test-collapse2" data=["toggle"=>"collapse"] role="button" aria=["expanded"=>"true","controls"=>"test-collapse"] title="Button title!"}
		Toggle 2
	{/button}
	{collapse id="test-collapse2" class="mt-2" visible=true}
	{card}Shown per default!{/card}
	{/collapse}

	<h3 class="mt-4">Accordion</h3>
	<div class="accordion" id="accordion">

	{card no-body=true title="Card title!"}
		{cardheader id="tab-1-head" data=["toggle" => "collapse", "target"=>"#tab-1"]}First{/cardheader}
		{collapse id="tab-1" class="mb-2" visible=true data=["parent"=>"#accordion"] aria=["labelledby"=>"tab-1-head"]}
			{cardbody}
				Does everybody know that pig named Lorem Ipsum? She's a disgusting pig, right?
				I write the best placeholder text, and I'm the biggest developer on the web by far...
				While that's mock-ups and this is politics, are they really so different?
			{/cardbody}
		{/collapse}
	{/card}
	{card no-body=true}
		{cardheader id="tab-3-head" data=["toggle" => "collapse", "target"=>"#tab-2"]}Second{/cardheader}
		{collapse id="tab-2" class="mb-2" data=["parent"=>"#accordion"] aria=["labelledby"=>"tab-2-head"]}
			{cardbody}
				I think the only difference between me and the other placeholder text is that I’m more honest and my words are more beautiful.
				I'm speaking with myself, number one, because I have a very good brain and I've said a lot of things.
				Look at that text! Would anyone use that? Can you imagine that, the text of your next webpage?! This placeholder text is gonna be HUGE.
			{/cardbody}
		{/collapse}
	{/card}

	{card no-body=true}
		{cardheader id="tab-3-head" data=["toggle" => "collapse", "target"=>"#tab-3"]}Third{/cardheader}
		{collapse id="tab-3" class="mb-2" data=["parent"=>"#accordion"] aria=["labelledby"=>"tab-3-head"]}
			{cardbody}
				All of the words in Lorem Ipsum have flirted with me - consciously or unconsciously.
				That's to be expected. Does everybody know that pig named Lorem Ipsum? She's a disgusting pig, right?
				Despite the constant negative ipsum covfefe. We have so many things that we have to do better... and certainly ipsum is one of them.
				An 'extremely credible source' has called my office and told me that Lorem Ipsum's birth certificate is a fraud.
			{/cardbody}
		{/collapse}
	{/card}
	</div>

	<h2 class="mt-4">Nav</h2>
	<div>
		{navbar toggleable="lg" type="dark" variant="info"}
			{navbarbrand href="#"}NavBar{/navbarbrand}

			{navbartoggle target="nav_collapse"}

			{collapse is-nav=true id="nav_collapse"}
				{navbarnav}
					{navitem href="#"}Link{/navitem}
					{navitem href="#" disabled=true}Disabled{/navitem}
				{/navbarnav}
				{navbarnav class="ml-auto"}
					{navitemdropdown text="Lang" right=true}
						{dropdownitem href="#"}EN{/dropdownitem}
						{dropdownitem href="#"}ES{/dropdownitem}
						{dropdownitem href="#"}RU{/dropdownitem}
						{dropdownitem href="#"}FA{/dropdownitem}
					{/navitemdropdown}

					{navitemdropdown text="User" right=true}
						{dropdownitem href="#"}Profile{/dropdownitem}
						{dropdownitem href="#"}Signout{/dropdownitem}
					{/navitemdropdown}
				{/navbarnav}
				{navform}
					{input size="sm" class="mr-sm-2" type="text" placeholder="Search"}
					{button size="sm" class="my-2 my-sm-0" type="submit"}
						Search
					{/button}
				{/navform}
			{/collapse}
		{/navbar}
	</div>

	<h2 class="mt-4">Progress</h2>
	{progress now=25 min=0 max=100 animated=true striped=true title="Progress title!"}<br>
	{progress now=50 min=0 max=100 animated=true striped=false type="danger"}<br>
	{progress now=75 min=0 max=100 animated=true striped=true type="success"}<br>
	{progress now=85 min=0 max=100 animated=false striped=true type="info" height="50px"}<br>

	<h2 class="mt-4">Embed</h2>
	<h3 class="mt-4">Youtube via iframe</h3>
	{embed type="iframe"
	aspect="16by9"
	src="https://www.youtube.com/embed/DLzxrzFCyOs"
	allowfullscreen=true}
	{/embed}
	<h3 class="mt-4">MP4 via video tag</h3>
	{embed type="video"
	aspect="16by9"
	poster="https://shop5.moches.de/big_buck_bunny.jpg"
	controls=true
	allowfullscreen=true}
		<source src="https://shop5.moches.de/big_buck_bunny.mp4"
		        type="video/mp4" />
	{/embed}

	{$address1 = ['plz' => '06108', 'name' => 'Halle/Saale', 'street' => 'Große-Ulrichstraße', 'no' => 49]}
	{$address2 = ['plz' => '41836', 'name' => 'Hückelhoven', 'street' => 'Rheinstraße', 'no' => 7]}

	{$address1 = (object)$address1}
	{$address2 = (object)$address2}

	{$test1 = ['id' => 1, 'name' => 'Falk', 'age' => 25, 'foo' => 'bar', 'address' => $address1]}
	{$test2 = ['id' => 2, 'name' => 'Marco', 'age' => 30, 'foo' => 'baz', 'address' => $address1]}
	{$test3 = ['id' => 3, 'name' => 'Felix', 'age' => 34, 'foo' => 'bar', 'address' => $address1]}
	{$test4 = ['id' => 4, 'name' => 'Danny', 'age' => 21, 'foo' => 'bar', 'address' => $address1]}
	{$test5 = ['id' => 5, 'name' => 'Clemens', 'age' => 112, 'foo' => 'bar', 'address' => $address1]}
	{$test6 = ['id' => 6, 'name' => 'David', 'age' => 55, 'foo' => 'bar', 'address' => $address2]}
	{$table1 = [(object)$test1, (object)$test2, (object)$test3, (object)$test4, (object)$test5, (object)$test6]}

	<h2 class="mt-4">Tables</h2>
	<h6 class="mt-4">Default</h6>
	{table fields=['id', 'name', 'age'] items=$table1 striped=true small=true caption="Small table without heading" responsive=true}

	<h6 class="mt-4">Labels</h6>
	{table fields=['id' => ['label' => '#'], 'name' => ['label' => 'Vorname'], 'age' => ['label' => 'Alter']]
		items=$table1 striped=true hover=true bordered=true caption="With labels" responsive="sm"}

	<h6 class="mt-4">Labels</h6>
	{table fields=['id' => ['label' => '#'], 'name' => ['label' => 'Vorname'], 'age' => ['label' => 'Alter'], 'address' => ['label' => 'PLZ', 'key' => 'plz']]
		items=$table1 striped=true hover=true bordered=true dark=true}


	{*<h2 class="mt-4">Pagination</h2>*}
	{*<h6>Default</h6>*}
	{*{pagination size="md" total-rows=100 per-page=10}*}
	{*{/pagination}*}
	{*<br>*}

	{*<h6>Small</h6>*}
	{*{pagination size="sm" total-rows=100 per-page=10}*}
	{*{/pagination}*}
	{*<br>*}

	{*<h6>Large</h6>*}
	{*{pagination size="lg" total-rows=100 per-page=10}*}
	{*{/pagination}*}

	<h2 class="mt-4">Grid</h2>
	<h3 class="mt-4">Equal width</h3>
	{container class="bv-example-row"}
	{row}
	{col}1 of 2{/col}
	{col}2 of 2{/col}
	{/row}
	{row}
	{col}1 of 3{/col}
	{col}2 of 3{/col}
	{col}3 of 3{/col}
	{/row}
	{/container}

	<h3 class="mt-4">Fixed width</h3>
	{container class="bv-example-row"}
	{row}
	{col}Column{/col}
	{col}Column{/col}
		<div class="w-100"></div>
	{col}Column{/col}
	{col}Column{/col}
	{/row}
	{/container}

	<h3 class="mt-4">Setting one column width</h3>
	{container class="bv-example-row"}
	{row class="text-center"}
	{col}1 of 3{/col}
	{col cols="a8"}2 of 3 (wider){/col}
	{col}3 of 3{/col}
	{/row}
	{row class="text-center"}
	{col}1 of 3{/col}
	{col cols="5"}2 of 3 (wider){/col}
	{col}3 of 3{/col}
	{/row}
	{/container}

	<h3 class="mt-4">Variable width content</h3>
	{container class="bv-example-row"}
	{row class="justify-content-md-center"}
	{col col=true lg="2"}1 of 3{/col}
	{col cols="12" md="auto"}Variable width content{/col}
	{col col=true lg="2"}3 of 3{/col}
	{/row}
	{row}
	{col}1 of 3{/col}
	{col cols="12" md="auto"}Variable width content{/col}
	{col col=true lg="2"}3 of 3{/col}
	{/row}
	{/container}

	<h3 class="mt-4">Equal-width multi-row</h3>
	{container class="bv-example-row"}
	{row}
	{col}col{/col}
	{col}col{/col}
		<div class="w-100"></div>
	{col}col{/col}
	{col}col{/col}
	{/row}
	{/container}

	<h3 class="mt-4">All breakpoints</h3>
	{container class="bv-example-row"}
	{row}
	{col}col{/col}
	{col}col{/col}
	{col}col{/col}
	{col}col{/col}
	{/row}
	{row}
	{col cols="8"}col-8{/col}
	{col cols="4"}col-4{/col}
	{/row}
	{/container}

	<h3 class="mt-4">Stacked to horizontal</h3>
	{container class="bv-example-row"}
	{row}
	{col sm="8"}col-sm-8{/col}
	{col sm="4"}col-sm-4{/col}
	{/row}
	{row}
	{col sm=true}col-sm{/col}
	{col sm=true}col-sm{/col}
	{col sm=true}col-sm{/col}
	{/row}
	{/container}

	<h3 class="mt-4">Mix and match</h3>
	{container class="bv-example-row"}
	{row}
	{col cols="12" md="8"}cols="12" md="8"{/col}
	{col cols="6" md="4"}cols="6" md="4"{/col}
	{/row}
	{row}
	{col cols="6" md="4"}cols="6" md="4"{/col}
	{col cols="6" md="4"}cols="6" md="4"{/col}
	{col cols="6" md="4"}cols="6" md="4"{/col}
	{/row}
	{row}
	{col cols="6"}cols="6"{/col}
	{col cols="6"}cols="6"{/col}
	{/row}
	{/container}

	<h3 class="mt-4">Vertical Alignment</h3>
	{container class="bv-example-row bv-example-row-flex-cols"}
	{row align-v="start"}
	{col}One of three columns start{/col}
	{col}One of three columns start{/col}
	{col}One of three columns start{/col}
	{/row}
	{row align-v="center"}
	{col}One of three columns center{/col}
	{col}One of three columns center{/col}
	{col}One of three columns center{/col}
	{/row}
	{row align-v="end"}
	{col}One of three columns end{/col}
	{col}One of three columns end{/col}
	{col}One of three columns end{/col}
	{/row}
	{/container}

	<h3 class="mt-4">Align-self</h3>
	{container class="bv-example-row bv-example-row-flex-cols" fluid=true}
	{row}
	{col align-self="start"}One of three columns{/col}
	{col align-self="center"}One of three columns{/col}
	{col align-self="end"}One of three columns{/col}
	{/row}
	{/container}

	<h3 class="mt-4">Horizontal Alignment</h3>
	{container class="bv-example-row"}
	{row align-h="start"}
	{col cols="4"}One of two columns start{/col}
	{col cols="4"}One of two columns start{/col}
	{/row}
	{row align-h="center"}
	{col cols="4"}One of two columns center{/col}
	{col cols="4"}One of two columns center{/col}
	{/row}
	{row align-h="end"}
	{col cols="4"}One of two columns end{/col}
	{col cols="4"}One of two columns end{/col}
	{/row}
	{row align-h="around"}
	{col cols="4"}One of two columns around{/col}
	{col cols="4"}One of two columns around{/col}
	{/row}
	{row align-h="between"}
	{col cols="4"}One of two columns between{/col}
	{col cols="4"}One of two columns between{/col}
	{/row}
	{/container}

	<h3 class="mt-4">Ordering columns</h3>
	{container fluid=true class="bv-example-row"}
	{row}
	{col}
		First, but unordered
	{/col}
	{col order="12"}
		Second, but last
	{/col}
	{col order="1"}
		Third, but first
	{/col}
	{/row}
	{/container}

	<h3 class="mt-4">Offsetting columns</h3>

	{container fluid=true class="bv-example-row"}
	{row}
	{col md="4"}md="4"{/col}
	{col md="4" offset-md="4"}md="4" offset-md="4"{/col}
	{/row}
	{row}
	{col md="3" offset-md="3"}md="3" offset-md="3"{/col}
	{col md="3" offset-md="3"}md="3" offset-md="3"{/col}
	{/row}
	{row}
	{col md="6" offset-md="3"}md="6" offset-md="3"{/col}
	{/row}
	{/container}

	<h2 class="mt-4">Form</h2>
	{form id="sampleform" action='https://shop5.moches.de' method='post'}
		{formgroup id="exampleInputGroup1" label="Email address:" label-for="exampleInput1" description="We'll never share your email with anyone else."}
			{input id="exampleInput1" type="email" required=true placeholder="Enter email"}
		{/formgroup}
		{formgroup id="exampleInputGroup2" label="Your Name:" label-for="exampleInput2"}
			{input id="exampleInput2" type="text" required=true placeholder="Enter name"}
		{/formgroup}
		{formgroup id="exampleInputGroup3" label="Food:" label-for="exampleInput3"}
			{select id="exampleInput3" required=true}
				<option value="carrot">Carrot</option>
				<option value="strawberry">Strawberry</option>
				<option value="raspberry">Respberry</option>
			{/select}
		{/formgroup}
		{formgroup id="exampleGroup4"}
			{checkboxgroup id="exampleChecks"}
				{checkbox value="me"}Check me out{/checkbox}
				{checkbox value="that"}Check that out{/checkbox}
			{/checkboxgroup}
		{/formgroup}
		{button type="submit" variant="primary"}Submit{/button}
		{button type="reset" variant="danger"}Reset{/button}
	{/form}
	<h3 class="mb-4">Inline form</h3>
	{form inline=true}
		<label class="sr-only" for="inlineFormInputName2">Name</label>
		{input class="mb-2 mr-sm-2 mb-sm-0" id="inlineFormInputName2" placeholder="Jane Doe"}
		<label class="sr-only" for="inlineFormInputGroupUsername2">Username</label>
		{inputgroup left="@" class="mb-2 mr-sm-2 mb-sm-0"}
			{input id="inlineFormInputGroupUsername2" placeholder="Username"}
		{/inputgroup}
		{checkbox class="mb-2 mr-sm-2 mb-sm-0"}Remember me{/checkbox}
		{button variant="primary"}Save{/button}
	{/form}

	<h2 class="mb-4">Input-Groups</h2>
	{inputgroup}
		{inputgroupprepend}
			{button variant="outline-info"}Button{/button}
		{/inputgroupprepend}
		{input type="number" min="0.00"}
		{inputgroupappend}
			{button variant="outline-secondary"}Button{/button}
			{button variant="outline-secondary"}Button{/button}
		{/inputgroupappend}
	{/inputgroup}
	<h3 class="mb-4">Sizing</h3>
	{foreach ['sm','','lg'] as $si}
		{inputgroup size=$si class="mb-3"}
			{inputgroupprepend is-text=true}Label{/inputgroupprepend}
			{input}
			{inputgroupappend}
				{button size=$si text="Button" variant="success"}Button{/button}
			{/inputgroupappend}
		{/inputgroup}
	{/foreach}

	{input type="number" placeholder="Number, 3 step!" min=3 max=33 step=3 size=40 style="width:250px"}
	{input type="text" placeholder="Text!" size=40 style="width:250px"}

	<h2 class="mt-4">Textarea</h2>
	{textarea class='mb-3' rows=4 placeholder='Enter something in these 4 rows'}{/textarea}
	{textarea class='mb-3' placeholder='You cannot enter anything' disabled=true}{/textarea}

	<h2 class="mt-4">Select</h2>

	{select class='mb-4'}
		<option name="op1">Option 1</option>
		<option name="op2">Option 2</option>
		<option name="op3">Option 3</option>
	{/select}
	{select size='lg' multiple=true }
		<option name="op1">Option 1</option>
		<option name="op2">Option 2</option>
		<option name="op3">Option 3</option>
	{/select}

	<h2 class="mt-4">Radio</h2>
	{formgroup label="Radios using sub-components"}
		{radiogroup id="radios1" name="radioSubComponent"}
			{radio name="rg1" value="first"}Toggle this custom radio{/radio}
			{radio name="rg1" value="second"}Or toggle this other custom radio{/radio}
			{radio name="rg1" value="third" disabled=true}This one is disabled{/radio}
			{radio name="rg1" value="fourth"}Fourth radio{/radio}
		{/radiogroup}
	{/formgroup}

	{formgroup label="Radios using sub-components - stacked"}
		{radiogroup id="radios1s" stacked=true name="radioSubComponentStacked"}
			{radio name="rg1s" value="first"}Toggle this custom radio{/radio}
			{radio name="rg1s" value="second"}Or toggle this other custom radio{/radio}
			{radio name="rg1s" value="third" disabled=true}This one is disabled{/radio}
			{radio name="rg1s" value="fourth"}Fourth radio{/radio}
		{/radiogroup}
	{/formgroup}

	{formgroup label="Radios using sub-components - plain"}
		{radiogroup id="radios2" name="radioSubComponent2" plain=true}
			{radio name="rg2" value="first"}Toggle this custom radio{/radio}
			{radio name="rg2" value="second"}Or toggle this other custom radio{/radio}
			{radio name="rg2" value="third" disabled=true}This one is disabled{/radio}
			{radio name="rg2" value="fourth"}Fourth radio{/radio}
		{/radiogroup}
	{/formgroup}

	{formgroup label="Radios using sub-components - stacked plain"}
		{radiogroup id="radios1sp"  plain=true stacked=true name="radioSubComponentStackedPlain"}
			{radio name="rg1sp" value="first"}Toggle this custom radio{/radio}
			{radio name="rg1sp" value="second"}Or toggle this other custom radio{/radio}
			{radio name="rg1sp" value="third" disabled=true}This one is disabled{/radio}
			{radio name="rg1sp" value="fourth"}Fourth radio{/radio}
		{/radiogroup}
	{/formgroup}


	{formgroup label="Radios in button style"}
		{radiogroup id="radios2" name="radioSubComponentButtons" button-variant="outline-primary" buttons=true size='lg'}
			{radio name="rg2b" value="first"}Toggle this custom radio{/radio}
			{radio name="rg2b" value="second"}Or toggle this other custom radio{/radio}
			{radio name="rg2b" value="third" disabled=true}This one is disabled{/radio}
			{radio name="rg2b" value="fourth"}Fourth radio{/radio}
		{/radiogroup}
	{/formgroup}

	{formgroup label="Radios in button style stacked"}
		{radiogroup id="radiobuttonsstacked" name="radioSubComponentButtonsStacked" buttons=true stacked=true size='lg'}
			{radio name="rg2bs" value="first"}Toggle this custom radio{/radio}
			{radio name="rg2bs" value="second"}Or toggle this other custom radio{/radio}
			{radio name="rg2bs" value="third" disabled=true}This one is disabled{/radio}
			{radio name="rg2bs" value="fourth"}Fourth radio{/radio}
		{/radiogroup}
	{/formgroup}

	<h2 class="mt-4">Inputs</h2>

	{assign var=inputTypes value=[
	'text', 'password', 'email', 'number', 'url',
	'tel', 'date', 'time', 'range', 'color'
	]}

	{foreach $inputTypes as $inputType}
		<div class="row my-1">
			<div class="col-sm-3">
				<label for="input-type-1-{$inputType}">Input type {$inputType}</label>
			</div>
			<div class="col-sm-9">
				{input id="input-type-1-{$inputType}" type=$inputType}
			</div>
		</div>
	{/foreach}

	<div class="row my-1">
		<div class="col-sm-3">
			<label for="input-type-text-sm">Input type text sm</label>
		</div>
		<div class="col-sm-9">
			{input id="input-type-text-sm" type="text" size="sm" placeholder="Enter something"}
		</div>
	</div>
	<div class="row my-1">
		<div class="col-sm-3">
			<label for="input-type-text-sm">Input type text lg</label>
		</div>
		<div class="col-sm-9">
			{input id="input-type-text-lg" type="text" size="lg" placeholder="Enter something"}
		</div>
	</div>

	<h2 class="mt-4">Grouped checkboxes</h2>
	{checkboxgroup id="checkboxes2" name="flavour2"}
		{checkbox value="orange"}Orange{/checkbox}
		{checkbox value="apple"}Apple{/checkbox}
		{checkbox value="pineapple"}Pinepple{/checkbox}
		{checkbox value="grape"}Grape{/checkbox}
	{/checkboxgroup}

	<h3>Stacked</h3>
	{checkboxgroup id="checkboxes2" name="flavour2" stacked=true}
		{checkbox value="orange"}Orange{/checkbox}
		{checkbox value="apple"}Apple{/checkbox}
		{checkbox value="pineapple"}Pinepple{/checkbox}
		{checkbox value="grape"}Grape{/checkbox}
	{/checkboxgroup}

	<h3>Plain inline</h3>
	{checkboxgroup id="checkboxes3" name="flavour3" stacked=false plain=true}
		{checkbox value="orange"}Orange{/checkbox}
		{checkbox value="apple"}Apple{/checkbox}
		{checkbox value="pineapple"}Pinepple{/checkbox}
		{checkbox value="grape"}Grape{/checkbox}
	{/checkboxgroup}

	<h3>Plain stacked</h3>
	{checkboxgroup id="checkboxes4" name="flavour4" stacked=true plain=true}
		{checkbox value="orange"}Orange{/checkbox}
		{checkbox value="apple"}Apple{/checkbox}
		{checkbox value="pineapple"}Pinepple{/checkbox}
		{checkbox value="grape"}Grape{/checkbox}
	{/checkboxgroup}

	<h3>Buttons primary lg</h3>
	{checkboxgroup id="checkboxes5" name="flavour5" buttons=true button-variant='primary' size='lg'}
		{checkbox value="orange"}Orange{/checkbox}
		{checkbox value="apple"}Apple{/checkbox}
		{checkbox value="pineapple"}Pinepple{/checkbox}
		{checkbox value="grape"}Grape{/checkbox}
	{/checkboxgroup}

	<h3>Buttons secondary sm stacked</h3>
	{checkboxgroup id="checkboxes6" name="flavour6" stacked=true buttons=true button-variant='secondary' size='sm'}
		{checkbox value="orange"}Orange{/checkbox}
		{checkbox value="apple"}Apple{/checkbox}
		{checkbox value="pineapple"}Pinepple{/checkbox}
		{checkbox value="grape"}Grape{/checkbox}
	{/checkboxgroup}

	<h2 class="mt-4">FormGroup</h2>
	{formgroup
	id="fieldset1"
	description="Let us know your name."
	label="Enter your name"
	label-for="input1"
	}
		{input id="input1"}
	{/formgroup}

	<h3 class="mt-1">horizontal</h3>
	{formgroup id="fieldsetHorizontal"
	horizontal=true
	label-cols="4"
	breakpoint="md"
	description="Let us know your name."
	label="Enter your name"
	label-for="inputHorizontal"}
		{input id="inputHorizontal"}
	{/formgroup}

	<h3 class="mt-1">Label size</h3>
	{formgroup horizontal=true
	label-cols="2"
	label-size="sm"
	label="Small"
	label-for="input_sm"}
		{input id="input_sm" size="sm"}
	{/formgroup}
	{formgroup horizontal=true
	label-cols="2"
	label="Default"
	label-for="input_default"}
	{input id="input_default"}
	{/formgroup}
	{formgroup horizontal=true
	label-cols="2"
	label-size="lg"
	label="Large"
	label-for="input_lg"}
		{input id="input_lg" size="lg"}
	{/formgroup}

	<h3 class="mt-1">Nested</h3>
	{card bg-variant="light"}
		{formgroup horizontal=true
		breakpoint="lg"
		label="Shipping Address"
		label-size="lg"
		label-class="font-weight-bold pt-0"
		class="mb-0"}
			{formgroup horizontal=true
			label="Street:"
			label-class="text-sm-right"
			label-for="nestedStreet"}
				{input id="nestedStreet"}
			{/formgroup}
			{formgroup horizontal=true
			label="City:"
			label-class="text-sm-right"
			label-for="nestedCity"}
				{input id="nestedCity"}
			{/formgroup}
			{formgroup horizontal=true
			label="State:"
			label-class="text-sm-right"
			label-for="nestedState"}
				{input id="nestedState"}
			{/formgroup}
			{formgroup horizontal=true
			label="Country:"
			label-class="text-sm-right"
			label-for="nestedCountry"}
				{input id="nestedCountry"}
			{/formgroup}
		{/formgroup}
	{/card}

	<h2 class="mt-4">Media</h2>
	{media id='media-test-id-1'}
		{mediaaside}
			{image src="https://lorempixel.com/128/240/" alt="Asideimage"}
		{/mediaaside}
		{mediabody}
			<h5 class="mt-0">Media Title</h5>
			<p>
				You’re disgusting. My text is long and beautiful, as, it has been well documented, are various other parts of my website.
				That other text? Sadly, it’s no longer a 10. I think the only card she has is the Lorem card.
				My placeholder text, I think, is going to end up being very good with women.
			</p>
			<p>Lorem Ipsum better hope that there are no "tapes" of our conversations before he starts leaking to the press!</p>
			<p class="mb-0">
				This placeholder text is gonna be HUGE. Some people have an ability to write placeholder text...
				It's an art you're basically born with. You either have it or you don't.
				The other thing with Lorem Ipsum is that you have to take out its family.
				I'm speaking with myself, number one, because I have a very good brain and I've said a lot of things.
			</p>
		{/mediabody}
	{/media}
	<h3 class="mt-1">Nested media</h3>
	{media no-body=true}
		{mediaaside}
			{image src="https://lorempixel.com/128/230/" alt="Asideimage 2"}
		{/mediaaside}
		{mediabody class="ml-3"}
			<h5 class="mt-0">Media Title</h5>
			<p>
				An ‘extremely credible source’ has called my office and told me that Barack Obama’s placeholder text is a fraud.
				I’m the best thing that ever happened to placeholder text. Be careful, or I will spill the beans on your placeholder text.
				This placeholder text is gonna be HUGE.
				You have so many different things placeholder text has to be able to do, and I don't believe Lorem Ipsum has the stamina.
			</p>
			<p class="mb-0">
				I think the only card she has is the Lorem card.
				An 'extremely credible source' has called my office and told me that Lorem Ipsum's birth certificate is a fraud.
			</p>
			{media}
				{mediaaside}
					{image src="https://lorempixel.com/64/64/" alt="Asideimage"}
				{/mediaaside}
				{mediabody}
					<h5 class="mt-0">Nested Media</h5>
					If Trump Ipsum weren’t my own words, perhaps I’d be dating it.
					Lorem Ipsum's father was with Lee Harvey Oswald prior to Oswald's being, you know, shot.
				{/mediabody}
			{/media}
		{/mediabody}
	{/media}

	<h2 class="mt-4">Carousel</h2>
	{carousel}
		{carouselslide caption="First slide"
		caption='Textcaption1'
		caption-text='Lorem ipsum1'
		content="Nulla vitae elit libero, a pharetra augue mollis interdum."
		img-src="https://lorempixel.com/1024/480/technics/2/"
		img-alt="Bild zwei"
		active=true}
		{/carouselslide}
		{carouselslide
		caption='Textcaption2'
		caption-text='Lorem ipsum2'
		img-src="https://lorempixel.com/1024/480/technics/4/"
		img-alt="Bild zwei"}
			<h1>Hello world!</h1>
		{/carouselslide}
		{carouselslide
		caption='Textcaption3'
		caption-text='Lorem ipsum3'
		img-alt="Bild drei"
		img-src="https://lorempixel.com/1024/480/technics/6/"}
			<h1>Hello world!</h1>
		{/carouselslide}
	{/carousel}
	<h3>Indicators</h3>
	{carousel indicators=true controls=true background="#ff0000" img-width=800 img-height=400}
		{carouselslide caption="First slide"
		caption="Textcaption1"
		caption-text="Lorem ipsum caption text"
		content="Nulla vitae elit libero, a pharetra augue mollis interdum."
		img-src="https://lorempixel.com/800/400/technics/1/"
		img-alt="Bild zwei"
		active=true}
			<h1>Bild eins!</h1>
		{/carouselslide}
		{carouselslide
		caption="Textcaption2"
		caption-text="Lorem ipsum2"
		img-src="https://lorempixel.com/800/400/technics/3/"
		img-alt="Bild zwei"}
			<h1>Bild zwei!</h1>
		{/carouselslide}
		{carouselslide
		caption="Textcaption3"
		caption-text="Lorem ipsum3"
		img-alt="Bild drei"
		img-src="https://lorempixel.com/800/400/technics/5/"}
			<h1>Bild drei!</h1>
		{/carouselslide}
		{carouselslide
		caption="Textcaption4"
		caption-text="Lorem ipsum4"
		img-alt="Bild vier"
		img-src="https://lorempixel.com/800/400/technics/7/"}
			<h1>Bild vier!</h1>
		{/carouselslide}
	{/carousel}

	<h2 class="mt-4">Buttongroup</h2>
	{buttongroup}
		{button variant="primary"}Button 1{/button}
		{button variant="primary"}Button 2{/button}
		{button variant="danger"}Button 3{/button}
		{button variant="warning"}Button 3{/button}
	{/buttongroup}
	<h3>Vertical</h3>

	{buttongroup vertical=true}
		{button variant="info"}Button 1{/button}
		{button variant="light"}Button 2{/button}
		{button variant="dark"}Button 3{/button}
		{button variant="warning"}Button 3{/button}
	{/buttongroup}

	<h2 class="mt-4">Dropdown</h2>

	{dropdown description="Dropdown Button"}
		{dropdownitem href="#"}
			Item1
		{/dropdownitem}
		{dropdownitem href="#"}
			Item2
		{/dropdownitem}
		{dropdownitem href="#"}
			Item3
		{/dropdownitem}
	{/dropdown}

	<h2 class="mt-4">Jumbotron</h2>
	{jumbotron header="Jumbotron Header!" lead="This is the lead."}
		<hr class="my-4">
		<p>He’s not a word hero. He’s a word hero because he was captured. I like text that wasn’t captured.</p>
		<p>I think my strongest asset maybe by far is my temperament. I have a placeholding temperament.</p>
		{button variant="primary"}Great stuff{/button}
	{/jumbotron}

	{jumbotron bg-variant="info" text-variant="white" border-variant="dark" header="Jumbotron Header!" lead="This is the lead."}
		<hr class="my-4">
		<p>All of the words in Lorem Ipsum have flirted with me - consciously or unconsciously. That's to be expected.</p>
		<p>That other text? Sadly, it’s no longer a 10.
			I think the only difference between me and the other placeholder text is that I’m more honest and my words are more beautiful.
			I think the only difference between me and the other placeholder text is that I’m more honest and my words are more beautiful.</p>
	{/jumbotron}

	<h2 class="mt-4">Tabs</h2>
	{tabs}
		{tab title="Tab 1" active=true}
			I think my strongest asset maybe by far is my temperament. I have a placeholding temperament.
			Lorem Ipsum is unattractive, both inside and out. I fully understand why it’s former users left it for something else.
			They made a good decision. My text is long and beautiful, as, it has been well documented, are various other parts of my website.
			Be careful, or I will spill the beans on your placeholder text. All of the words in Lorem Ipsum have flirted with me - consciously or unconsciously.
			That's to be expected
		{/tab}
		{tab title="Tab 2"}
			All of the words in Lorem Ipsum have flirted with me - consciously or unconsciously. That's to be expected.
		{/tab}
		{tab title="Tab 3"}
			Despite the constant negative ipsum covfefe. When other websites give you text, they’re not sending the best.
			They’re not sending you, they’re sending words that have lots of problems and they’re bringing those problems with us.
			They’re bringing mistakes. They’re bringing misspellings. They’re typists… And some, I assume, are good words.
		{/tab}
		{tab title="Tab 4 disabled" disabled=true}
			Lorem Ipsum's father was with Lee Harvey Oswald prior to Oswald's being, you know, shot. He’s not a word hero.
			He’s a word hero because he was captured. I like text that wasn’t captured.
			My placeholder text, I think, is going to end up being very good with women.
			We are going to make placeholder text great again. Greater than ever before.
		{/tab}
	{/tabs}
	<br>

	<h2 class="mt-4">Images</h2>
	<div>
		<h5>Small image with <code>fluid</code>:</h5>
		{image src="https://lorempixel.com/300/150/" fluid=true alt="Fluid image"}
		<h5 class="my-3">Small image with <code>fluid-grow</code>:</h5>
		{image src="https://lorempixel.com/300/150/" fluid-grow=true alt="Fluid-Grow image"}
	</div>
	<br>

	<div class="bd-example vue-example vue-example-b-img-thumbnail">
		<div class="p-4 bg-dark container-fluid">
			<div class="row">
				<div class="col">
					{image src="https://lorempixel.com/250/250/technics/4/"
					alt="Thumbnail"
					thumbnail=true
					fluid=true}
				</div>
				<div class="col">
					{image src="https://lorempixel.com/250/250/technics/5/"
					alt="Thumbnail"
					thumbnail=true
					fluid=true}
				</div>
				<div class="col">
					{image src="https://lorempixel.com/250/250/technics/6/"
					alt="Thumbnail"
					thumbnail=true
					fluid=true}
				</div>
			</div>
		</div>
	</div>

	<br>
	{image src="https://lorempixel.com/60/60/" rounded=true alt="rounded" class="m-1"}
	{image src="https://lorempixel.com/60/60/" rounded="top" alt="top" class="m-1"}
	{image src="https://lorempixel.com/60/60/" rounded="right" alt="top" class="m-1"}
	{image src="https://lorempixel.com/60/60/" rounded="left" alt="left" class="m-1"}
	{image src="https://lorempixel.com/60/60/" rounded="circle" alt="circle" class="m-1"}
	{image src="https://lorempixel.com/60/60/" rounded="0" alt="0" class="m-1"}

	<h3>Alignment</h3>
	<div class="clearfix">
		{image src="https://lorempixel.com/201/201/" height=200 width=200 left=true alt="Left"}
		{image src="https://lorempixel.com/202/202/" height=200 width=200 right=true alt="Right"}
	</div>
	<br>
	{image src="https://lorempixel.com/200/200/" center=true alt="Center"}
	<br>


	<h3>Height/Width</h3>
	{image src="https://lorempixel.com/400/400/" height=300 width=250 alt="300x250"}

	<h2 class="mt-4">Alerts</h2>
	<div>
		{alert show=true}Default Alert{/alert}
		{alert show=true variant="primary"}Primary Alert{/alert}
		{alert show=true variant="secondary"}Secondary Alert{/alert}
		{alert show=true variant="success"}Success Alert{/alert}
		{alert show=true variant="danger"}Danger Alert{/alert}
		{alert show=true variant="warning"}Warning Alert{/alert}
		{alert show=true variant="info"}Info Alert{/alert}
		{alert show=true variant="light"}Light Alert{/alert}
		{alert show=true variant="dark"}Dark Alert{/alert}
	</div>

	<h3>Dismissable</h3>

	{alert show=true dismissible=true}Default Alert dismissable{/alert}
	{alert show=true dismissible=true variant="primary"}Primary Alert dismissable{/alert}
	{alert show=true dismissible=true variant="secondary"}Secondary Alert dismissable{/alert}
	{alert show=true dismissible=true variant="success"}Success Alert dismissable{/alert}
	{alert show=true dismissible=true variant="danger"}Danger Alert dismissable{/alert}
	{alert show=true dismissible=true variant="warning"}Warning Alert dismissable{/alert}
	{alert show=true dismissible=true variant="info"}Info Alert dismissable{/alert}
	{alert show=true dismissible=true variant="light"}Light Alert dismissable{/alert}
	{alert show=true dismissible=true variant="dark"}Dark Alert dismissable{/alert}

	<h2 class="mt-4">Link</h2>
	{link href="https://shop5.moches.de/" rel="noopener"}This is a link with rel=noopen!!{/link}
	{link href="https://shop5.moches.de/" target="_blank"}This is a link with target=_blank!{/link}
	<h2 class="mt-4">Card</h2>
	{card
	title="Card mit Bild!"
	subtitle="Some subtitle..."
	img-src="https://shop5.moches.de/media/image/product/15511/sm/sonderpreis-bei-einem-lagernden-artikel-sonderpreis-gilt-bis-lagerbestand-10.jpg"
	img-alt="Image"
	tag="article"
	style="max-width: 20rem;"
	links=['link nummer 1', 'link nummer 2']
	class="mb-4"
	}
		<p class="card-text">
			Dies ist der Fließtext und gleich kommt auch noch ein Input:
		</p>
		<p>{input type='number' placeholder='Placeholder!'}</p>
	{/card}
	<br>
	{card
	title="Card mit Header/Footer und Footer-Tag"
	header="This is the header"
	footer-tag="footer"
	footer="This is the footer"
	subtitle="Some subtitle..."
	tag="article"
	style="max-width: 20rem;"
	links=['link nummer 1', 'link nummer 2']
	class="mb-4"
	}
		<p class="card-text">
			Some quick example text to build on the card title and make up the bulk of the card's content.
		</p>
		<p>{input type='number' placeholder='Placeholder!'}</p>
	{/card}

	<h2 class="mt-4">Badge</h2>
	{badge}Testbadge{/badge}
	{badge active=true}Testbadge active{/badge}
	{badge disabled=true}Testbadge disabled{/badge}
	{badge variant="primary"}Primary{/badge}
	{badge variant="primary" disabled=true}Testbadge primary disabled{/badge}
	{badge variant="secondary"}Secondary{/badge}
	{badge variant="success"}Success{/badge}
	{badge variant="warning"}Warning{/badge}
	{badge variant="danger"}Danger{/badge}
	{badge variant="warning"}Warning{/badge}
	{badge variant="info"}Info{/badge}
	{badge variant="light"}Light{/badge}
	{badge variant="dark"}Dark{/badge}

	<h2 class="mt-4">ListGroup</h2>
	{listgroup}
		{listgroupitem id='el1'}Element 1 mit id{/listgroupitem}
		{listgroupitem}Element 2{/listgroupitem}
		{listgroupitem}Noch eins{/listgroupitem}
	{/listgroup}
	<br>
	{listgroup}
		{listgroupitem tag="button"}Element 1 als Button{/listgroupitem}
		{listgroupitem tag="button"}Element 2 als Button{/listgroupitem}
		{listgroupitem tag="button"}Noch eins{/listgroupitem}
	{/listgroup}
	<br>
	{listgroup title="LG Title!"}
		{listgroupitem variant="primary"}Element primary{/listgroupitem}
		{listgroupitem variant="secondary"}Element secondary{/listgroupitem}
		{listgroupitem variant="success"}Element success{/listgroupitem}
		{listgroupitem variant="danger"}Element danger{/listgroupitem}
		{listgroupitem variant="warning"}Element warning{/listgroupitem}
		{listgroupitem variant="info"}Element info{/listgroupitem}
		{listgroupitem variant="light"}Element light{/listgroupitem}
		{listgroupitem variant="dark"}Element dark{/listgroupitem}
	{/listgroup}
	<br>
	{listgroup tag="ul"}
		{listgroupitem tag="li"}Element 1 - li{/listgroupitem}
		{listgroupitem tag="li" active=true}Element 2 - li active{/listgroupitem}
		{listgroupitem tag="li"}Noch eins - li{/listgroupitem}
		{listgroupitem tag="li" disabled=true}disabled li{/listgroupitem}
	{/listgroup}

	{card header="Card with listgroup" tag="article" style="max-width: 20rem;" hasBody=false class="mt-4 mb-4"}
		{listgroup class='list-group-flush'}
			{listgroupitem href='https://shop5.moches.de'}Element 1 - mit Link{/listgroupitem}
			{listgroupitem}Element 2 - no link{/listgroupitem}
			{listgroupitem href='#'}Noch eins - li mit Badge{badge variant='info'}Info{/badge}{/listgroupitem}
		{/listgroup}
	{/card}

	<h2 class="mt-4">Modal</h2>
	{modal title="Titel des Modals" id="exampleModal" centered=true}
		Dies ist der Inhalt des Modals mitsamt einem Input {input type="text" value="Beispiel"}
	{/modal}
	{button variant="primary" data=['toggle'=>'modal', 'target'=>'#exampleModal']}Open modal dialog{/button}
	<hr>

</div>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>
</body>
</html>
