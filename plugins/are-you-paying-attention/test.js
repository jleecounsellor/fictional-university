wp.blocks.registerBlockType("ourplugin/are-you-paying-attention", {
    title: "Are You Paying Attention?",
    icon: "smiley", 
    category: "common", 
    //post editor screen display
    edit: function () {
        //create HTML element: type of HTML el, properties (class,styles), text
        return wp.element.createElement("h3", null, "Hello, this is from the admin editor screen")
    },
    //displayed in content 
    save: function () {
        //create HTML element: type of HTML el, properties (class,styles), text
        return wp.element.createElement("h1", null, "Hello, this is the front end")
    }
})