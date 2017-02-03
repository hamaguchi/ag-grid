<?php
$key = "Cell Rendering";
$pageTitle = "ag-Grid Cell Rendering";
$pageDescription = "You can customise every cell in ag-Grid. This is done by providing cell renderers. This page describe creating cell renderers.";
$pageKeyboards = "ag-Grid Cell Renderers";
include '../documentation-main/documentation_header.php';
?>

<h2>Cell Rendering</h2>

<p>
    Cell Rendering - this is a very powerful feature in ag-Grid. With this, you can put whatever
    you want in the grid. The job of the grid is to lay out the cells. What goes into the cells,
    that's where you come in! You customise the rendering inside the cells by providing 'cellRenderers'.
</p>

<p>
    You configure cellRenderers as part of the column definition and can be one of the following:
<ul>
    <li>function: The cellRenderer is a function that gets called once for each cell. The function
        should return a string (which will be treated as html) or a DOM object. Use this if you
        have no cleanup or refresh requirements of the cell - it's a 'fire and forget' approach
        to the cell rendering.</li>
    <li>component: The grid will call 'new' on the provided class and treat the object as a component, using
        lifecycle methods. Use this if you need to do cleanup when the cell is removed or have
        refresh requirements.</li>
    <li>string: The cellRenderer is looked up from the provided cellRenderers. Use this if you
        want to use a built in renderer (eg 'group') or you want to register your own cellRenderers
        for reuse.</li>
</ul>
</p>

<h3>cellRenderer Function</h3>

<p>
    The easiest (but not as flexible) way to provide your own cellRenderer is to provide a function.
    The function takes a set of parameters (with information on what to render) and you return back
    either a) a string of HTML or b) a DOM object.
</p>

<p>
    Below are some simple examples of cellRenderer function:
</p>

<pre><code><span class="codeComment">// put the value in bold</span>
colDef.cellRenderer = function(params) {
    return '&lt;b>' + params.value.toUpperCase() + '&lt;/b>';
}

<span class="codeComment">// put a tooltip on the value</span>
colDef.cellRenderer = function(params) {
    return '&lt;span title="the tooltip">'+params.value+'&lt;/span>';
}

<span class="codeComment">// create a DOM object </span>
colDef.cellRenderer = function(params) {
    var eDiv = document.createElement('div');
    eDiv.innerHTML = '&lt;span class="my-css-class">&lt;button class="btn-simple">Push Me&lt;/button>&lt;/span>';
    var eButton = eDiv.querySelectorAll('.btn-simple')[0];

    var eButton.addEventListener('click', function() {
        console.log('button was clicked!!');
    });

    return eDiv;
}</code></pre>

<p>
    See further below for the set of parameters passed to the rendering function.
</p>

<h3>cellRenderer Component</h3>

<p>
    The most flexible (but a little more tricky) way to provide a cellRenderer is to provide a component class.
    The component class that you provide can have callback methods on it for refresh and destroy.
</p>

<note>
    <p>
        A cellRenderer Component is an ag-Grid concept that is similar in how 'components' work in other frameworks.
        Other than sharing the same concept and name, ag-Grid Components have nothing to do with Angular components,
        React components, or any other components.
    </p>
    <p>
        An ag-Grid cellRenderer Component does not need to extend any class or do anything except implement the
        methods shown in the interface.
    </p>
</note>

<p>
    The interface for the cellRenderer component is as follows:
</p>

<pre>interface ICellRenderer {
    <span class="codeComment">// Optional - Params for rendering. The same params that are passed to the cellRenderer function.</span>
    init?(params: any): void;

    <span class="codeComment">// Mandatory - Return the DOM element of your editor, this is what the grid puts into the DOM</span>
    getGui(): HTMLElement;

    <span class="codeComment">// Optional - Gets called once by grid after editing is finished - if your editor needs to do any cleanup,</span>
    <span class="codeComment">// do it here</span>
    destroy?(): void;

    <span class="codeComment">// Optional - Get the cell to refresh. If this method is not provided, then when refresh is needed, the grid</span>
    <span class="codeComment">// will remove the component from the DOM and create a new component in it's place with the new values.</span>
    refresh?(params: any): void;
}</pre>

    <p>
        Below is a simple example of cellRenderer class:
    </p>

<pre><span class="codeComment">// function to act as a class</span>
function MyCellRenderer () {}

<span class="codeComment">// gets called once before the renderer is used</span>
MyCellRenderer.prototype.init = function(params) {
    <span class="codeComment">// create the cell</span>
    this.eGui = document.createElement('div');
    this.eGui.innerHTML = '&lt;span class="my-css-class">&lt;button class="btn-simple">Push Me&lt;/button>&lt;span class="my-value">&lt;/span>&lt;/span>';

    <span class="codeComment">// get references to the elements we want</span>
    this.eButton = this.eGui.querySelectorAll('.btn-simple')[0];
    this.eValue = this.eGui.querySelectorAll('.my-value')[0];

    <span class="codeComment">// set value into cell</span>
    this.eValue.innerHTML = params.valueFormatted ? params.valueFormatted : params.value;

    <span class="codeComment">// add event listener to button</span>
    this.eventListener = function() {
        console.log('button was clicked!!');
    };
    this.eButton.addEventListener('click', this.eventListener);
};

<span class="codeComment">// gets called once when grid ready to insert the element</span>
MyCellRenderer.prototype.getGui = function() {
    return this.eGui;
};

<span class="codeComment">// gets called whenever the user gets the cell to refresh</span>
MyCellRenderer.prototype.refresh = function(params) {
    <span class="codeComment">// set value into cell again</span>
    this.eValue.innerHTML = params.valueFormatted ? params.valueFormatted : params.value;
};

<span class="codeComment">// gets called when the cell is removed from the grid</span>
MyCellRenderer.prototype.destroy = function() {
    <span class="codeComment">// do cleanup, remove event listener from button</span>
    this.eButton.removeEventListener('click', this.eventListener);
};</code></pre>

<h3>cellRenderer Component Refresh</h3>

<p>
    The grid is constantly refreshing rows and cells into the browser, but not every refresh of the grid
    results in the refresh method of your cellRenderer getting called. The following details when your
    cellRenderer refresh method gets called and when not.
</p>

<p>
    The following will result in cellRenderer refresh method getting called:
<ul>
    <li>
        Calling <i>rowNode.setDataValue(colKey, value)</i> to set a value directly onto the rowNode
    </li>
    <li>
        When editing a cell and editing is stopped, so that cell displays new value after editing.
    </li>
    <li>
        Calling <i>api.refreshCells(rowNodes, colIds)</i> to inform grid data has changed (see <a href="../javascript-grid-refresh/">Refresh</a>).
    </li>
    <li>
        Calling <i>api.softRefreshView()</i> to inform grid data has changed (see <a href="../javascript-grid-refresh/">Refresh</a>).
    </li>
</ul>
If any of the above occur, the <i>refresh()</i> method will be called if it is provided. If not,
the component will be destroyed and replaced.
</p>

<p>
    The following will <b>not</b> result in cellRenderer refresh method getting called:
<ul>
    <li>
        Calling <i>rowNode.setData(data)</i> to set new data into a rowNode.
    </li>
    <li>
        Scrolling the grid vertically (results in rows getting ripped in / out of the dom).
    </li>
    <li>
        All other api refresh methods (<i>refreshRows, refreshView</i> etc).
    </li>
</ul>
All of the above will result in the component getting destrotyed and recreated.
</p>

<h3>
    cellRenderer Component Lifecycle
</h3>

<p>
    The lifecycle of the cellRenderer is as follows:
<ul>
    <li><i>new</i> is called on the class.</li>
    <li><i>init()</i> is called once.</li>
    <li><i>getGui()</i> is called once.</li>
    <li><i>refresh()</i> is called 0..n times (ie it may never be called, or called multiple times)</li>
    <li><i>destroy()</i> is called once.</li>
</ul>
In other words, <i>new(), init(), getGui()</i> and <i>destroy()</i> are always called exactly once.
<i>refresh()</i> is optionally called multiple times.
</p>

<p>
    If you are implementing <i>refresh()</i>, remember that <i>getGui()</i> is only called once, so be sure
    to update the existing GUI in your refresh, do not think that the grid is going to call <i>getGui()</i>
    again to get a new version of the GUI.
</p>

<h3>cellRenderer Params</h3>

<p>
    The cellRenderer function and cellRenderer component take parameters as follows:
<ul>
    <li><b>cellRenderer function: </b> Passed to function.</li>
    <li><b>cellRenderer component: </b> Passed to init method.</li>
</ul>
The parameters are identical regardless of which cellRenderer type you use and contain the following:
</p>

<table class="table">
    <tr>
        <th>Value</th>
        <th>Description</th>
    </tr>
    <tr>
        <th>value</th>
        <td>The value to be rendered.</td>
    </tr>
    <tr>
        <th>valueFormatted</th>
        <td>If a valueFormatter was provided, is the result of calling the formatter.</td>
    </tr>
    <tr>
        <th>valueGetter</th>
        <td>A function, that when called, gives you the value, calling the relevant valueGetter / expression
            if necessary. This can be called at any time after rendering should the value change and you
            find the refresh functionality provided by the grid is not enough.</td>
    </tr>
    <tr>
        <th>formatValue</th>
        <td>A function, that when called, formats the value. The valueFormatted attribute already gives
            you the formatted value, however you can call this if you need to format another value,
            maybe you need to format a different value (this is used by the provided 'animation' cellRenderers
            where they need to format the delta difference).</td>
    </tr>
    <tr>
        <th>node</th>
        <td>The RowNode of the row being rendered.</td>
    </tr>
    <tr>
        <th>data</th>
        <td>The row (from the rowData array, where value was taken) been rendered.</td>
    </tr>
    <tr>
        <th>column</th>
        <td>The column been rendered (in ag-Grid, each colDef is wrapped by a Column).</td>
    </tr>
    <tr>
        <th>colDef</th>
        <td>The colDef been rendered.</td>
    </tr>
    <tr>
        <th>$scope</th>
        <td>If compiling to Angular, is the row's child scope, otherwise null.</td>
    </tr>
    <tr>
        <th>rowIndex</th>
        <td>The index of the row, after sorting and filtering.</td>
    </tr>
    <tr>
        <th>api</th>
        <td>A reference to the grid api.</td>
    </tr>
    <tr>
        <th>columnApi</th>
        <td>A reference to the column api.</td>
    </tr>
    <tr>
        <th>context</th>
        <td>The context as set on the gridOptions.</td>
    </tr>
    <tr>
        <th>refreshCell</th>
        <td>A callback function, to tell the grid to refresh this cell and reapply all css styles and classes.
            Useful if you update the data for the cell and want to just render again from scratch.</td>
    </tr>
    <tr>
        <th>eGridCell</th>
        <td>A reference to the DOM element representing the grid cell that your component will live inside.
            Useful if you want to add event listeners or classes at this level. This is the DOM element that
            gets browser focus when selecting cells.</td>
    </tr>
    <tr>
        <th>eParentOfValue</th>
        <td>If using checkbox selection, your component will live inside eParentOfValue which sits beside a
            checkbox and both live inside eGridCell.</td>
    </tr>

</table>

<h3>Complementing cellRenderer Params</h3>

<p>
    On top of the parameters provided by the grid, you can also provide your own parameters. This is useful if
    you want to 'configure' your cellRenderer. For example, you might have a cellRenderer for formatting
    currency but you need to provide what currency for your cellRenderer to use.
</p>

<p>
    Provide params to a cellRenderer using the colDef option cellRendererParams.
</p>

<pre><code><span class="codeComment">// define cellRenderer to be reused</span>
var myCellRenderer = function(params) {
    return '&lt;span style="color: '+params.color+'">' + params.value + '&lt;/span>';
}

<span class="codeComment">// use with a color</span>
colDef.cellRenderer = myCellRenderer;
colDef.cellRendererParams = {
    color: 'guinnessBlack'
}

<span class="codeComment">// use with another color</span>
colDef.cellRenderer = myCellRenderer;
colDef.cellRendererParams = {
    color: 'irishGreen'
}</code></pre>

<h3>Provided cellRenderers</h3>

<p>Instead of providing a cellRenderer function or component, you can select from ones that come with the
    grid or install your own into the grid.</p>

<p>
    The cellRenderers provided by the grid are as follows:
<ul>
    <li><b>group</b>: For group rendering.</li>
    <li><b>animateShowChange</b>: Cell renderer that when data changes, it animates showing the difference by showing the delta for a period of time.</li>
    <li><b>animateSlide</b>: Cell renderer that when data changes, it animates showing the old value fading away to the left.</li>
</ul>
</p>

<p>
    From the provided cellRenderers, it is intended the you use 'group' as is, however
    'animateShowChange' and 'animateSlide' are given as examples on what is possible. How you show
    changes or otherwise want to refresh is going to be different for everyone. So take influence from
    what you see, but consider creating your own.
</p>

<p>Usage of group cellRenderer is detailed in the section <a href="../javascript-grid-grouping/">Grouping Rows and Aggregation</a>.</p>

<p>Usage of animateShowChange and animateSlide is demonstrated in the section <a href="../javascript-grid-viewport/">Viewport</a>.</p>

<!--

TAKING OUT as want to reconsider how to register components

<h3>Registering cellRenderers</h3>

<p>
    You do not need to register your cellRenderers. However if you do, they will be stored alongside the grid
    provided cellRenderers and will be available to your column definitions and identified by strings. This
    is useful if you want to provide your own cellRenders inside your company that are reused
    across grids. It also means you can define your columns using only JSON and not require
    referencing JavaScript functions directly.
</p>

<p>
    You register cellRenders in one of the following ways:
    <ul>
    <li>Provide <i>cellRenderers</i> property to the grid as a map of key=>cellRenderer pairs.
    This property is used once during grid initialisation.</li>
    <li>Register the cellRenderer by calling <i>gridApi.addCellRenderer(key, cellRenderer)</i>.
    This can be called at any time during the lifetime of the grid.</li>
</ul>
</p>
-->
<p>If you have many instances of a grid, you must register the cellRenders with each one.</p>

<h3>Example: Using cellRenderers</h3>

<p>
    The example below shows five columns formatted, demonstrating each of the
    methods above.
</p>
<ul>
    <li>'Month' column uses cellStyle to format each cell in the column with the same style.</li>
    <li>'Max Temp' and 'Min Temp' columns uses the Function method to format each cell in the column with the same style.</li>
    <li>'Days of Air Frost' column uses the Component method to format each cell in the column with the same style</li>
    <li>'Days Sunshine' and 'Rainfall (10mm)' use simple functions to display icons.</li>
</ul>

<show-example example="example2"></show-example>

<h3>Cell Renderers and Row Groups</h3>

<p>
    If you are mixing cellRenderers and row grouping, then you need to understand that the value and / or data
    may be missing in the group row. The data and value will be missing if you are not doing any aggregations
    (hence no data at the group level) and the value will be missing if you are doing aggregations but not
    on the column the cellRenderer is on.
</p>
<p>
    This is simply fixed by checking for the existence of the data before you use it like the following:
    <pre>
colDef.cellRenderer = function(params) {
    <span class="codeComment">// check the data exists, to avoid error when grouping but not aggregating</span>
    if (params.data) {
        <span class="codeComment">// data exists, so we can access it</span>
        return '&lt;b>'+params.data.theBoldValue+'&lt;/b>';
    } else {
        <span class="codeComment">// when we return null, the grid will display a blank cell</span>
        return null;
    }
};
</pre>
</p>
<!-- React from here on -->
<h2 id="reactCellRendering">
    <img src="../images/react_large.png" style="width: 60px;"/>
    React Cell Rendering
</h2>

<p>
    It is possible to provide a React cellRenderer for ag-Grid to use. All of the information above is
    relevant to React cellRenderers. This section explains how to apply this logic to your React component.
</p>

<p>
    For examples on React cellRendering, see the
    <a href="https://github.com/ceolter/ag-grid-react-example">ag-grid-react-example</a> on Github.
    In the example, both 'Skills' and 'Proficiency' columns use React cellRenderers. The Country column
    uses a standard ag-Grid cellRenderer, to demonstrate both working side by side.</p>
</p>

<h3><img src="../images/react_large.png" style="width: 20px;"/> Specifying a React cellRenderer</h3>

<p>
    If you are using the ag-grid-react component to create the ag-Grid instance,
    then you will have the option of additionally specifying the cellRenderers
    as React components.
</p>

<pre><span class="codeComment">// create your cellRenderer as a React component</span>
class NameCellRenderer extends React.Component {
    render() {
    <span class="codeComment">// put in render logic</span>
        return &lt;span>{this.props.value}&lt;/span>;
    }
}

<span class="codeComment">// then reference the Component in your colDef like this</span>
colDef = {

    <span class="codeComment">// instead of cellRenderer we use cellRendererFramework</span>
    cellRendererFramework: NameCellRenderer

    <span class="codeComment">// specify all the other fields as normal</span>
    headerName: 'Name',
    field: 'firstName',
    ...
}</pre>

<p>
    By using <i>colDef.cellRendererFramework</i> (instead of <i>colDef.cellRenderer</i>) the grid
    will know it's a React component, based on the fact that you are using the React version of
    ag-Grid.
</p>

<p>
    This same mechanism can be to use a React Component in the following locations:
<ul>
    <li>colDef.cellRenderer<b>Framework</b></li>
    <li>colDef.floatingCellRenderer<b>Framework</b></li>
    <li>gridOptions.fullWidthCellRenderer<b>Framework</b></li>
    <li>gridOptions.groupRowRenderer<b>Framework</b></li>
    <li>gridOptions.groupRowInnerRenderer<b>Framework</b></li>
</ul>
In other words, wherever you specify a normal cellRenderer, you can now specify a React cellRenderer
in the property of the same name excepting ending 'Framework'. As long as you are using the React ag-Grid component,
the grid will know the framework to use is React.
</p>

<h3><img src="../images/react_large.png" style="width: 20px;"/> React Props</h3>

<p>
    The React component will get the 'cellRenderer Params' as described above as it's React Props.
    Therefore you can access all the parameters as React Props.

<pre><span class="codeComment">// React cellRenderer Component</span>
class NameCellRenderer extends React.Component {

    <span class="codeComment">// did you know that React passes props to your component constructor??</span>
    constructor(props) {
        super(props);
        <span class="codeComment">// from here you can access any of the props!</span>
        console.log('The value is ' + props.value);
        <span class="codeComment">// we can even call grid API functions, if that was useful</span>
        props.api.selectAll();
    }

    render() {
        <span class="codeComment">// or access props using 'this'</span>
        return &lt;span>{this.props.value}&lt;/span>;
    }
}</pre>
</p>

<h3><img src="../images/react_large.png" style="width: 20px;"/> React Methods / Lifecycle</h3>

<p>
    All of the methods in the ICellRenderer interface described above are applicable
    to the React Component with the following exceptions:
<ul>
    <li><i>init()</i> is not used. Instead use the React props passed to your Component.</li>
    <li><i>destroy()</i> is not used. Instead use the React <i>componentWillUnmount()</i> method for
        any cleanup you need to do.</li>
    <li><i>getGui()</i> is not used. Instead do normal React magic in your <i>render()</i> method..</li>
</ul>

<h3><img src="../images/react_large.png" style="width: 20px;"/> Handling Refresh</h3>

<p>
    You have the option of handling refresh or not by either providing a <i>refresh()</i> method on
    your React component or not. If not present, then the grid will destroy your component and create
    a new one if it tries to refresh the cell. If you do implement it, then it's up to your React
    components <i>refresh()</i> method to update the state of your component.
</p>

<!-- Angular from here -->
<h2 id="ng2CellRendering">
    <img src="../images/angular2_large.png" style="width: 60px;"/>
    Angular Cell Rendering
</h2>

<p>
    It is possible to provide a Angular cellRenderers for ag-Grid to use. All of the information above is
    relevant to Angular cellRenderers. This section explains how to apply this logic to your Angular component.
</p>

<p>
    For examples on Angular cellRendering, see the
    <a href="https://github.com/ceolter/ag-grid-ng2-example">ag-grid-ng2-example</a> on Github.
    Angular Renderers are used on all but the first Grid on this example page (the first grid uses plain JavaScript Renderers)</p>
</p>

<h3><img src="../images/angular2_large.png" style="width: 20px;"/> Specifying a Angular cellRenderer</h3>

<p>
    If you are using the ag-grid-ng2 component to create the ag-Grid instance,
    then you will have the option of additionally specifying the cellRenderers
    as Angular components.

<h3><img src="../images/angular2_large.png" style="width: 20px;"/> cellRenderers from Angular Components</h3>
<pre><span class="codeComment">// create your cellRenderer as a Angular component</span>
@Component({
    selector: 'square-cell',
    template: `{{valueSquared()}}`
})
class SquareComponent implements AgRendererComponent {
    private params:any;

    agInit(params:any):void {
        this.params = params;
    }

    private valueSquared():number {
        return this.params.value * this.params.value;
    }
}
<span class="codeComment">// then reference the Component in your colDef like this</span>
colDef = {
    {
        headerName: "Square Component",
        field: "value",
        <span class="codeComment">// instead of cellRenderer we use cellRendererFramework</span>
        cellRendererFramework: SquareComponent

        <span class="codeComment">// specify all the other fields as normal</span>
        editable:true,
        colId: "square",
        width: 200
    }
}</pre>

<p>Your Angular components need to implement <code>AgRendererComponent</code>.
    The ag Framework expects to find the <code>agInit</code> method on the created component, and uses it to supply the cell <code>params</code>.<p>

<p>
    By using <i>colDef.cellRendererFramework</i> (instead of <i>colDef.cellRenderer</i>) the grid
    will know it's a Angular component, based on the fact that you are using the Angular version of
    ag-Grid.
</p>

<p>
    This same mechanism can be to use a Angular Component in the following locations:
<ul>
    <li>colDef.cellRenderer<b>Framework</b></li>
    <li>colDef.floatingCellRenderer<b>Framework</b></li>
    <li>gridOptions.fullWidthCellRenderer<b>Framework</b></li>
    <li>gridOptions.groupRowRenderer<b>Framework</b></li>
    <li>gridOptions.groupRowInnerRenderer<b>Framework</b></li>
</ul>
In other words, wherever you specify a normal cellRenderer, you can now specify a Angular cellRenderer
in the property of the same name excepting ending 'Framework'. As long as you are using the Angular ag-Grid component,
the grid will know the framework to use is Angular.
</p>

<h3>Example: Rendering using Angular Components</h3>
<p>
    Using Angular Components in the Cell Renderers
</p>
<show-example example="../ng2-example/index.html?example=from-component"
              jsfile="../ng2-example/app/from-component.component.ts"
              html="../ng2-example/app/from-component.component.html"></show-example>

<h3><img src="../images/angular2_large.png" style="width: 20px;"/> Angular Methods / Lifecycle</h3>

<p>
    All of the methods in the ICellRenderer interface described above are applicable
    to the Angular Component with the following exceptions:
<ul>
    <li><i>init()</i> is not used. Instead implement the <code>agInit</code> method (on the <code>AgRendererComponent</code> interface).</li>
    <li><i>destroy()</i> is not used. Instead implement the Angular<code>OnDestroy</code> interface (<code>ngOnDestroy</code>) for
        any cleanup you need to do.</li>
    <li><i>getGui()</i> is not used. Instead do normal Angular magic in your Component via the Angular template.</li>
</ul>

<h3><img src="../images/angular2_large.png" style="width: 20px;"/> Handling Refresh</h3>

<p>To receive update (for example, after an edit) you should implement the optional <code>refresh</code> method on the <code>AgRendererComponent</code> interface.</p>

<h3>Example: Rendering using more complex Angular Components</h3>
<p>
    Using more complex Angular Components in the Cell Renderers
</p>
<show-example example="../ng2-example/index.html?example=from-rich-component"
              jsfile="../ng2-example/app/from-rich.component.ts"
              html="../ng2-example/app/from-rich.component.html"></show-example>

<note>The full <a href="https://github.com/ceolter/ag-grid-ng2-example">ag-grid-ng2-example</a> repo shows many more examples for rendering, including grouped rows, full width renderers
    and so on, as well as examples on using Angular Components with both CellEditors and Filters</note>

<!-- vuejs from here -->
<h2 id="vueCellRendering">
    <img src="../images/vue_large.png" style="width: 60px;"/>
    VueJS Cell Rendering
</h2>

<p>
    It is possible to provide a VueJS cellRenderers for ag-Grid to use. All of the information above is
    relevant to VueJS cellRenderers. This section explains how to apply this logic to your VueJS component.
</p>

<p>
    For examples on VueJS cellRendering, see the
    <a href="https://github.com/ceolter/ag-grid-vue-example">ag-grid-vue-example</a> on Github.
    VueJS Renderers are used on all but the first Grid on this example page (the first grid uses plain JavaScript Renderers)</p>
</p>

<h3><img src="../images/vue_large.png" style="width: 20px;"/> Specifying a VueJS cellRenderer</h3>

<p>
    If you are using the ag-grid-vue component to create the ag-Grid instance,
    then you will have the option of additionally specifying the cellRenderers
    as VueJS components.</p>

    <p>A VueJS component can be defined in a few different ways (please see <a href="/best-vuejs-data-grid#define_component">
        Defining VueJS Components</a> for all the options), but in this example we're going to define our renderer as a Single File Component:</p>

<pre ng-non-bindable><span class="codeComment">// create your cellRenderer as a VueJS component</span>
&lt;template&gt;
    &lt;span class="currency"&gt;{{ params.value | currency('EUR') }}&lt;/span&gt;
&lt;/template&gt;

&lt;script&gt;
    import Vue from "vue";

    export default Vue.extend({
        filters: {
            currency(value, symbol) {
                let result = value;
                if (!isNaN(value)) {
                    result = value.toFixed(2);
                }
                return symbol ? symbol + result : result;
            }
        }
    });
&lt;/script&gt;

&lt;style scoped&gt;
    .currency {
        color: blue;
    }
&lt;/style&gt;

<span class="codeComment">// then reference the Component in your colDef like this</span>
{
    <span class="codeComment">// instead of cellRenderer we use cellRendererFramework</span>
    cellRendererFramework: CurrencyComponent,

    <span class="codeComment">// specify all the other fields as normal</span>
    headerName: "Currency (Filter)",
    field: "currency",
    colId: "params",
    width: 150
}
</pre>

<p>The Grid cell's value will be made available implicitly in a data value names <code>params</code>. This value will be available to
    you from the <code>created</code> VueJS lifecycle hook.</p>

<p>You can think of this as you having defined the following:</p>
<pre>
export default {
    data () {
        return {
            params: null
        }
    },
    ...
</pre>

<p>but you do not need to do this - this is made available to you behind the scenes, and contains the cells value.</p>

<p>
    By using <i>colDef.cellRendererFramework</i> (instead of <i>colDef.cellRenderer</i>) the grid
    will know it's a VueJS component, based on the fact that you are using the VueJS version of
    ag-Grid.
</p>

<p>
    This same mechanism can be to use a VueJS Component in the following locations:
<ul>
    <li>colDef.cellRenderer<b>Framework</b></li>
    <li>colDef.floatingCellRenderer<b>Framework</b></li>
    <li>gridOptions.fullWidthCellRenderer<b>Framework</b></li>
    <li>gridOptions.groupRowRenderer<b>Framework</b></li>
    <li>gridOptions.groupRowInnerRenderer<b>Framework</b></li>
</ul>
In other words, wherever you specify a normal cellRenderer, you can now specify a VueJS cellRenderer
in the property of the same name excepting ending 'Framework'. As long as you are using the VueJS ag-Grid component,
the grid will know the framework to use is VueJS.
</p>

<h3>Example: Rendering using VueJS Components</h3>
<p>
    Using VueJS Components in the Cell Renderers
</p>
<show-example url="../vue-examples/#/dynamic"
              jsfile="../vue-examples/src/dynamic-component-example/DynamicComponentExample.vue"
              exampleHeight="525px"></show-example>

<h3><img src="../images/vue_large.png" style="width: 20px;"/> VueJS Methods / Lifecycle</h3>

<p>
    All of the methods in the ICellRenderer interface described above are applicable
    to the VueJS Component with the following exceptions:
<ul>
    <li><i>init()</i> is not used. The cells value is made available implicitly via a data field called <code>params</code>.</li>
    <li><i>getGui()</i> is not used. Instead do normal VueJS magic in your Component via the VueJS template.</li>
</ul>

<h3><img src="../images/vue_large.png" style="width: 20px;"/> Handling Refresh</h3>

<p>To receive update (for example, after an edit) you should implement the optional <code>refresh</code> method on the <code>AgRendererComponent</code> interface.</p>

<h3>Example: Rendering using more complex VueJS Components</h3>
<p>
    Using more complex VueJS Components in the Cell Renderers
</p>
<show-example url="../vue-examples/#/rich-dynamic"
              jsfile="../vue-examples/src/rich-dynamic-component-example/RichDynamicComponentExample.vue"
              exampleHeight="525px"></show-example>

<note>The full <a href="https://github.com/ceolter/ag-grid-vue-example">ag-grid-vue-example</a> repo shows many more examples for rendering, including grouped rows, full width renderers
    and so on, as well as examples on using VueJS Components with both CellEditors and Filters</note>

<!-- Aurelia from here -->
<h2 id="aureliaCellRendering">
    <img src="../images/aurelia_large.png" style="width: 60px;"/>
    Aurelia Cell Rendering
</h2>

<p>
    It is possible to provide Aurelia Component cellRenderers for ag-Grid to use, with support for two way binding. All of the information above is
    relevant to Aurelia cellRenderers. This section explains how to apply this logic to your Aurelia component.
</p>

<p>
    For examples on Aurelia cellRendering, see the
    <a href="https://github.com/ceolter/ag-grid-ng2-example">ag-grid-aurelia-example</a> on Github.</p>
</p>

<h3><img src="../images/aurelia_large.png" style="width: 20px;"/> Specifying a Aurelia cellRenderer</h3>

<p>
    If you are using the ag-grid-aurelia component to create the ag-Grid instance,
    then you will have the option of additionally specifying the cellRenderers
    as aurelia components. You have two options that are described below:
<ol>
    <li>Templates</li>
    <li>cellRenderer Functions or cellRenderer Components</li>
</ol>

    We only mention Templates here - using functions or regular components is discussed above in earlier sections.
</p>


<h3><img src="../images/aurelia_large.png" style="width: 20px;"/> cellRenderers from Templates</h3>
<pre><span class="codeComment">// create your cellRenderer as an Aurelia template</span>
&lt;ag-grid-aurelia #agGrid style="width: 100%; height: 100%;" class="ag-fresh"
                 grid-options.bind="gridOptions">
  &lt;ag-grid-column header-name="Mood" field="mood" width.bind="150" editable.bind="true">
    &lt;ag-cell-template>
      &lt;img width="20px" if.bind="params.value === 'Happy'" src="images/smiley.png"/>
      &lt;img width="20px" if.bind="params.value !== 'Happy'" src="images/smiley-sad.png"/>
    &lt;/ag-cell-template>
  &lt;/ag-grid-column>
&lt;/ag-grid-aurelia>
</pre>

<p>The advantage of using Templates for your renderers is that you have the ability to use Aurelia's dynamic data binding facilties.</p>

<h3>Example: Rendering using Templates</h3>
<p>
    Using Templates in the Cell Renderers - here we're using a Template (<code>ag-cell-template</code>) to render the Mood column depending on the underlying value
    of the "mood" field.
</p>

<show-example example="../aurelia-example/#/editor/true"
              jsfile="../aurelia-example/components/editor-example/editor-example.ts"
              html="../aurelia-example/components/editor-example/editor-example.html"></show-example>

<h3><img src="../images/aurelia_large.png" style="width: 20px;"/> Aurelia Methods / Lifecycle</h3>


<h3>Example: Rendering using regular cellRenderer Components</h3>
<p>
    A Rich Grid leveraging regular Cell Renderer Components
</p>

<show-example example="../aurelia-example/#/richgrid-declarative/true"
          jsfile="../aurelia-example/components/rich-grid-declarative-example/rich-grid-declarative-example.ts"
          html="../aurelia-example/components/rich-grid-declarative-example/rich-grid-declarative-example.html"
          exampleHeight="525px"></show-example>

<br/>
<note>The full <a href="https://github.com/ceolter/ag-grid-aurelia-example">ag-grid-aurelia-example</a> repo shows many more examples for rendering, including grouped rows, full width renderers and filters.</note>

<?php include '../documentation-main/documentation_footer.php';?>

