import $ from 'jquery';

class Find {
    // 1. describe and initiates our object
    constructor() {
        //Add Search HTML
        this.addSearchHTML(); 

        //locating items in our html
        this.openButton = $(".js-search-trigger"); 
        this.closeButton = $(".search-overlay__close"); 
        this.resultsDiv = $("#search-overlay__results");
        this.searchOverlay = $(".search-overlay"); 
        this.searchField = $("#search-term"); 

        //add event listeners to page after variables are created above
        this.events(); 

        //state of overlay to make sure method runs once
        this.isOverlayOpen = false; 
        //reset timer if user starts typing within time limit
        this.typingTimer; 
        //track of the state of the spinner so it doesn't restart
        this.isSpinnerVisible = false; 
        //track the search value so if you hit the back button, it'll still search
        this.previousValue; 
    }
    // 2. events
    events() {
        this.openButton.on("click", this.openOverlay.bind(this)); 
        this.closeButton.on("click", this.closeOverlay.bind(this)); 
        $(document).on("keydown", this.keyPressDispatcher.bind(this));
        this.searchField.on("keyup", this.typingLogic.bind(this)); 
    }

    // 3. methods
    typingLogic() {
        //if the new value doesn't match the previous value in any way, do this
        if (this.searchField.val() != this.previousValue) {
            //each keypress uses its own timeout, so this it to ensure its only once
            clearTimeout(this.typingTimer); 
            if (this.searchField.val()) {
                if (!this.isSpinnerVisible){
                    this.resultsDiv.html('<div class="spinner-loader"></div>'); 
                    this.isSpinnerVisible = true; 
                }
                this.typingTimer = setTimeout(this.getResults.bind(this), 750);
            } else {
                //if there is no value in the search field
                this.resultsDiv.html(''); 
                this.isSpinnerVisible = false; 
            }


        }
        this.previousValue = this.searchField.val(); 
    }

    getResults() {
        // arrow function => doesn't change the value of "this" so we don't need to bind the "this" in resultsDiv
        $.getJSON(universityData.root_url + '/wp-json/university/v1/search?term=' + this.searchField.val(), (results) => {
            this.resultsDiv.html(`
                <div class="row">
                    <div class="one-third">
                        <h2 class="search-overlay__section-title">General Information</h2>
                        ${results.generalInfo.length ? '<ul class="link-list min-list">' : '<p>No general information matches that search.</p>'}
                            ${results.generalInfo.map(data => `<li><a href="${data.permalink}">${data.title}</a> ${data.postType == 'post' ? ` by ${data.authorName}` : ''} </li>`).join('')}
                        ${results.generalInfo.length ? '</ul>' : ''}
                    </div>
                    <div class="one-third">
                        <h2 class="search-overlay__section-title">Program(s)</h2>
                        ${results.programs.length ? '<ul class="link-list min-list">' : `<p>No programs match that search. <a href="${universityData.root_url}/programs">View all programs.</a></p>`}
                            ${results.programs.map(data => `<li><a href="${data.permalink}">${data.title}</a></li>`).join('')}
                        ${results.programs.length ? '</ul>' : ''}

                        <h2 class="search-overlay__section-title">Professor(s)</h2>
                        ${results.professors.length ? '<ul class="professor-cards">' : `<p>No professors match that search.</p>`}
                            ${results.professors.map(data => `
                                    <li class="professor-card__list-item">
                                        <a class="professor-card" href="${data.permalink}">
                                            <img class="professor-card__image" src="${data.image}">
                                            <span class="professor-card__name">${data.title}</span>
                                        </a>
                                    </li>
                                `).join('')}
                        ${results.professors.length ? '</ul>' : ''}

                    </div>
                    <div class="one-third">
                        <h2 class="search-overlay__section-title">Campus(es)</h2>
                        ${results.campuses.length ? '<ul class="link-list min-list">' : `<p>No campuses match that search. <a href="${universityData.root_url}/campuses">View all campuses.</a></p>`}
                            ${results.campuses.map(data => `<li><a href="${data.permalink}">${data.title}</a></li>`).join('')}
                        ${results.campuses.length ? '</ul>' : ''}

                        <h2 class="search-overlay__section-title">Events</h2>
                        ${results.events.length ? '' : `<p>No events match that search. <a href="${universityData.root_url}/events">View all events.</a></p>`}
                            ${results.events.map(data => `
                                <div class="event-summary">
                                    <a class="event-summary__date t-center" href="${data.permalink}">
                                        <span class="event-summary__month">${data.month}</span>
                                        <span class="event-summary__day">${data.day}</span>
                                    </a>
                                    <div class="event-summary__content">
                                        <h5 class="event-summary__title headline headline--tiny"><a href="${data.permalink}">${data.title}</a></h5>
                                        <p>${data.description}<a href="${data.permalink}" class="nu gray">Learn more</a></p>
                                    </div>
                                </div>
                                `).join('')}
                    </div>
                </div>
                `); 
                this.isSpinnerVisible = false;
        }); 
    }

    keyPressDispatcher(event) {
        //if "s" is pressed, the overlay is not already open, and there isn't focus on another input field...
        if(event.keyCode == 83 && !this.isOverlayOpen && !$("input, textarea").is(':focus')) {
            this.openOverlay(); 
        }
        if(event.keyCode == 27 && this.isOverlayOpen) {
            this.closeOverlay(); 
        }
    }

    openOverlay() {
        //add the overlay css
        this.searchOverlay.addClass("search-overlay--active");
        //remove the overlay scroll
        $("body").addClass("body-no-scroll"); 
        //wait until element is visible before focusing
        setTimeout(() => this.searchField.focus(), 301);
        this.isOverlayOpen = true; 
    }

    closeOverlay() {
        //remove the overlay css
        this.searchOverlay.removeClass("search-overlay--active");
        //add scroll back to body
        $("body").removeClass("body-no-scroll"); 
        this.isOverlayOpen = false; 
        //remove content for the next time the overlay is active
        this.searchField.val(''); 
    }

    addSearchHTML(){
        $("body").append(`
            <div class="search-overlay">
                <div class="search-overlay__top">
                    <div class="container"> 
                        <i class="fa fa-search search-overlay__icon" aria-hidden="true"></i>
                        <input type="text" class="search-term" placeholder="What are you looking for?" id="search-term" autocomplete="off">
                        <i class="fa fa-window-close search-overlay__close" aria-hidden="true"></i>
                    </div>
                </div>
                <div class="container">
                    <div id="search-overlay__results"></div>
                </div> 
            </div>
        `);
    }
}

export default Find
