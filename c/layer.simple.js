/**
 * Simple Layer constructor.
 * @constructor
 */

function SimpleLayer(){

    this.dom_obj = this.create_block();
    document.body.appendChild(this.dom_obj);

    this.container = document.createElement('div');
    this.container.addClass('simple_layer');
    this.dom_obj.appendChild(this.container);

    this.base_layer = BaseLayer.prototype;
}

SimpleLayer.prototype = new BaseLayer();

loader.next();