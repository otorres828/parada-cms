<input x-data="checkAll" x-ref="checkAllCheckbox" @change="handleChange" class="form-check-input" type="checkbox">

@script

    <script>

        Alpine.data('checkAll', () => {

            return {

                init(){

                    this.$wire.watch('selectedRecordIds', () => {
                            
                       this.updateCheckAllState();

                    });

                    this.$wire.watch('recordIdsOnPage', () => {

                        this.updateCheckAllState();

                    })
                },

                 pageIsSelected() {

                    if(this.$wire.recordIdsOnPage.length>0) {

                        return this.$wire.recordIdsOnPage.every(id => this.$wire.selectedRecordIds.includes(id));

                    } else{

                        return false;
                    }

                   
                },

                pageIsEmpty() {
                    return this.$wire.selectedRecordIds.length === 0;
                },

                handleChange(e) {

                    e.target.checked ? this.selectAll() : this.deselectAll()                
                    
                },

                selectAll() {

                    this.$wire.recordIdsOnPage.forEach(id => {

                        if (this.$wire.selectedRecordIds.includes(id)) return;

                        this.$wire.selectedRecordIds.push(id);
                    });

                },

                deselectAll() {
                   this.$wire.selectedRecordIds = []
                },

                updateCheckAllState() {


                    if (this.pageIsSelected()) {

                        this.$refs.checkAllCheckbox.checked = true;

                    } else {

                        this.$refs.checkAllCheckbox.checked = false;
                    }
                
                }

            }
        });
    
    </script>

@endscript