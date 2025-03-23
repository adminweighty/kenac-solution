<template>
    <div>

        <!-- Dropdown for Add Transaction -->
        <div class="dropdown mb-4">
            <button class="btn btn-primary" type="button" @click="openAddTransactionModal"   data-bs-toggle="modal"
                    :data-bs-target="'#addTransactionModal'">
                Add Transaction
            </button>

        </div>
        <!-- Filters Section -->
        <div class="filters d-flex align-items-center small-font">
            <input type="text" v-model="filters.reference" placeholder="Filter by Reference"
                   class="form-control mb-2 mr-2"/>
            <input type="number" v-model="filters.amount" placeholder="Filter by Amount"
                   class="form-control mb-2 mr-2"/>
            <input type="date" v-model="filters.transaction_date" placeholder="Filter by Transaction Date"
                   class="form-control mb-2 mr-2"/>
            <input type="date" v-model="filters.value_date" placeholder="Filter by Value Date"
                   class="form-control mb-2 mr-2"/>
        </div>

        <div class="row">
            <div class="col-md-12">
                <button class="btn btn-secondary btn-sm" @click="resetFilters">Reset Filters</button>
            </div>
        </div>

        <!-- Transaction Table -->
        <!-- Transaction Table -->
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">Transactions</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped small-table">
                    <thead>
                    <tr>
                        <th @click="sortTable('id')">ID <i :class="getSortIcon('id')"></i></th>
                        <th @click="sortTable('reference')">Reference <i :class="getSortIcon('reference')"></i></th>
                        <th @click="sortTable('amount')">Amount <i :class="getSortIcon('amount')"></i></th>
                        <th @click="sortTable('transaction_date')">Transaction Date <i
                            :class="getSortIcon('transaction_date')"></i></th>
                        <th @click="sortTable('value_date')">Value Date <i :class="getSortIcon('value_date')"></i></th>
                        <th>Type</th>
                        <th>Send to Bank</th>
                        <th>Bank Confirmed</th>
                        <th>Bank Date</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="transaction in paginatedTransactions" :key="transaction.id">
                        <td>{{ transaction.id }}</td>
                        <td>{{ transaction.reference }}</td>
                        <td>{{ transaction.amount }}</td>
                        <td>{{ formatDate(transaction.transaction_date) }}</td>
                        <td>{{ formatDate(transaction.value_date) }}</td>
                        <td>{{ capitalizeFirstLetter(transaction.payment_type) }}</td>
                        <td>
                            <input v-if="transaction.send_to_bank===1" type="checkbox" checked class="green-checkbox"
                                   disabled/>
                            <input
                                v-if="transaction.send_to_bank !== 1"
                                type="checkbox"
                                :value="transaction.id"
                                :checked="tickedTransactions.includes(transaction.id)"
                                @change="toggleTransactionSelection(transaction)"
                            />
                        </td>
                        <td>
                            <input v-if="transaction.bank_confirmed===1" type="checkbox" checked class="green-checkbox"
                                   disabled/>
                            <input v-if="transaction.bank_confirmed!==1" type="checkbox" disabled/>
                        </td>
                        <td v-if="transaction.bank_confirmation_date!=null">
                            {{ formatDate(transaction.bank_confirmation_date) }}
                        </td>
                        <td v-if="transaction.bank_confirmation_date==null"><span class="text-danger">Pending</span>
                        </td>
                        <td>
                            <button v-if="transaction.send_to_bank!==1" class="btn btn-warning btn-sm"
                                    @click="editTransaction(transaction)" data-bs-toggle="modal" data-bs-target="#editTransactionModal" >
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button v-if="transaction.send_to_bank!==1" class="btn btn-danger btn-sm button-spacing"
                                    @click="confirmDelete(transaction)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Pagination Controls -->
        <div class="pagination">
            <button class="btn btn-primary btn-sm" :disabled="currentPage === 1" @click="changePage(currentPage - 1)">
                Previous
            </button>

            <!-- Pagination Numbers -->
            <button
                v-for="page in pageNumbers"
                :key="page"
                class="btn btn-light btn-sm"
                :class="{ active: currentPage === page }"
                @click="changePage(page)">
                {{ page }}
            </button>

            <button class="btn btn-primary btn-sm" :disabled="currentPage === totalPages"
                    @click="changePage(currentPage + 1)">Next
            </button>
        </div>
        <div class="row">
            <br/>
            <div class="col-md-12 mt-5">
                <button class="btn btn-success" @click="sendTransactionsToBank"
                        :disabled="tickedTransactions.length === 0">
                    Send Selected Transactions to Bank
                </button>
            </div>
        </div>

        <!-- Add Transaction Modal -->
        <div class="modal fade" id="addTransactionModal" tabindex="-1" role="dialog"
             aria-labelledby="addTransactionModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addTransactionModalLabel">Add New Transaction</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Form Inputs for Adding Transaction -->
                        <div class="form-group">
                            <label for="reference">Reference</label>
                            <input type="text" class="form-control" id="reference" v-model="newTransaction.reference"/>
                            <p
                                class="text-center"
                                style="color: red"
                                v-if="error.reference != null"
                            >
                                {{ error.reference }}
                            </p>
                        </div>
                        <div class="form-group">
                            <label for="amount">Amount</label>
                            <input type="number" class="form-control" id="amount" v-model="newTransaction.amount"/>
                            <p
                                class="text-center"
                                style="color: red"
                                v-if="error.amount != null"
                            >
                                {{ error.amount }}
                            </p>
                        </div>
                        <div class="form-group">
                            <label for="transaction_date">Transaction Date</label>
                            <input type="date" class="form-control" id="transaction_date"
                                   v-model="newTransaction.transaction_date"/>
                            <p
                                class="text-center"
                                style="color: red"
                                v-if="error.transaction_date != null"
                            >
                                {{ error.transaction_date }}
                            </p>
                        </div>
                        <div class="form-group">
                            <label for="value_date">Value Date</label>
                            <input type="date" class="form-control" id="value_date"
                                   v-model="newTransaction.value_date"/>
                            <p
                                class="text-center"
                                style="color: red"
                                v-if="error.value_date != null"
                            >
                                {{ error.value_date }}
                            </p>
                        </div>
                        <!-- Payment Type Radio Button Group -->
                        <div class="form-group">
                            <label for="payment_type">Payment Type</label><br/>
                            <input type="radio" id="mobile" value="mobile" v-model="newTransaction.payment_type"/>
                            Mobile
                            <input type="radio" id="visa" value="visa" v-model="newTransaction.payment_type"/> Visa
                            <input type="radio" id="mastercard" value="mastercard"
                                   v-model="newTransaction.payment_type"/> Mastercard
                            <input type="radio" id="eft" value="eft" v-model="newTransaction.payment_type"/> EFT
                        </div>
                        <p
                            class="text-center"
                            style="color: red"
                            v-if="error.payment_type != null"
                        >
                            {{ error.payment_type }}
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"  data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" @click="saveTransaction">
                            <span v-if="isLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            <span v-else>Save changes</span>

                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Edit Transaction Modal -->
        <div class="modal fade" id="editTransactionModal" tabindex="-1" role="dialog"
             aria-labelledby="editTransactionModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTransactionModalLabel">Edit Transaction</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Form Inputs for Editing -->
                        <div class="form-group" :class="{'has-error': !currentTransaction.reference}">
                            <label for="reference">Reference</label>
                            <input type="text" class="form-control" id="reference"
                                   v-model="currentTransaction.reference"/>
                            <p
                                class="text-center"
                                style="color: red"
                                v-if="error.reference != null"
                            >
                                {{ error.reference }}
                            </p>
                        </div>
                        <div class="form-group" :class="{'has-error': !currentTransaction.amount}">
                            <label for="amount">Amount</label>
                            <input type="number" class="form-control" id="amount" v-model="currentTransaction.amount"/>
                            <p
                                class="text-center"
                                style="color: red"
                                v-if="error.amount != null"
                            >
                                {{ error.amount }}
                            </p></div>
                        <div class="form-group" :class="{'has-error': !currentTransaction.transaction_date}">
                            <label for="transaction_date">Transaction Date</label>
                            <input type="date" class="form-control" id="transaction_date"
                                   v-model="currentTransaction.transaction_date"/>
                            <p
                                class="text-center"
                                style="color: red"
                                v-if="error.transaction_date != null"
                            >
                                {{ error.transaction_date }}
                            </p>
                        </div>
                        <div class="form-group" :class="{'has-error': !currentTransaction.value_date}">
                            <label for="value_date">Value Date</label>
                            <input type="date" class="form-control" id="value_date"
                                   v-model="currentTransaction.value_date"/>
                            <p
                                class="text-center"
                                style="color: red"
                                v-if="error.value_date != null"
                            >
                                {{ error.value_date }}
                            </p>
                        </div>
                        <!-- Payment Type Radio Button Group -->
                        <div class="form-group">
                            <label for="payment_type">Payment Type</label><br/>
                            <input type="radio" id="mobile" value="mobile" v-model="currentTransaction.payment_type"/>
                            Mobile
                            <input type="radio" id="visa" value="visa" v-model="currentTransaction.payment_type"/> Visa
                            <input type="radio" id="mastercard" value="mastercard"
                                   v-model="currentTransaction.payment_type"/> Mastercard
                            <input type="radio" id="eft" value="eft" v-model="currentTransaction.payment_type"/> EFT
                        </div>
                        <p
                            class="text-center"
                            style="color: red"
                            v-if="error.payment_type != null"
                        >
                            {{ error.payment_type }}
                        </p>
                    </div>
                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" @click="saveTransaction"
                                :disabled="isFormInvalid">
                            <span v-if="isLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            <span v-else>Save changes</span>

                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
.button-spacing {
    margin-left: 8px;
}

.pagination button {
    margin: 0 5px;
}

.pagination .btn-info {
    background-color: #17a2b8;
    color: white;
}
.spinner-border {
    border-top-color: transparent; /* Optional: Customize spinner color */
}
/* Remove the default checkbox styling */
/* Remove the default checkbox styling */
/* Remove the default checkbox styling */
.green-checkbox {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 4px;
    border: 2px solid #ccc;
    background-color: #fff;
    position: relative;
    cursor: not-allowed; /* Show a non-clickable cursor */
}

/* Style for the green checkbox when checked and disabled */
.green-checkbox:checked:disabled {
    background-color: #4caf50 !important; /* Green background */
    border-color: #4caf50 !important; /* Green border */
    accent-color: #4caf50; /* Green checkmark */
    cursor: not-allowed; /* Make it non-interactive */
}

/* Custom white checkmark appearance */
.green-checkbox:checked:disabled::after {
    content: '';
    position: absolute;
    top: 50%; /* Center the tick vertically */
    left: 6px;
    width: 6px;
    height: 12px;
    border: solid 2px #fff; /* White border for the checkmark */
    border-width: 0 2px 2px 0; /* Create a checkmark shape */
    transform: rotate(45deg); /* Rotate to make the tick */
    transform-origin: center; /* Ensure it rotates around the center */
    margin-top: -6px; /* Adjust for perfect vertical centering */
}

.small-table {
    font-size: 12px; /* Adjust to the size you prefer */
}

.small-table td,
.small-table th {
    padding: 6px 8px; /* Adjust padding to make content fit better */
}

.small-font {
    font-size: 10px; /* Adjust this value as needed */
}

.small-font .form-control {
    font-size: 10px; /* Adjust input font size */
    padding: 4px 8px; /* Adjust padding for a more compact look */
}

</style>
<script>
import Swal from 'sweetalert2';
import axios from 'axios';
import {format, parseISO} from "date-fns";


export default {
    data() {
        return {
            isLoading: false, // To control the loader
            transactions: [],
            tickedTransactions: [],// To store the selected transaction IDs
            currentPage: 1,
            itemsPerPage: 10,
            filters: {
                reference: '',
                amount: '',
                transaction_date: '',
                value_date: '',
                payment_type: ''
            },
            error: {
                reference: '',
                amount: '',
                transaction_date: '',
                value_date: '',
                payment_type: ''
            },
            newTransaction: {
                reference: '',
                amount: '',
                transaction_date: '',
                value_date: '',
                payment_type: ''
            },
            currentTransaction: {},
            sortColumn: '',
            sortOrder: 'asc', // or 'desc'
        };
    },
    computed: {
        // Apply filters first, then sorting, and finally pagination
        filteredAndSortedTransactions() {
            const filtered = this.transactions.filter((transaction) => {
                return (
                    (!this.filters.reference ||
                        transaction.reference.toLowerCase().includes(this.filters.reference.toLowerCase())) &&
                    (!this.filters.amount || transaction.amount === parseFloat(this.filters.amount)) &&
                    (!this.filters.transaction_date || transaction.transaction_date.includes(this.filters.transaction_date)) &&
                    (!this.filters.value_date || transaction.value_date.includes(this.filters.value_date))
                );
            });

            // Apply sorting to the filtered transactions
            const sorted = filtered.sort((a, b) => {
                const valueA = a[this.sortColumn];
                const valueB = b[this.sortColumn];

                if (this.sortColumn === 'transaction_date' || this.sortColumn === 'value_date') {
                    return this.compareDates(valueA, valueB);
                }

                if (valueA < valueB) {
                    return this.sortOrder === 'asc' ? -1 : 1;
                } else if (valueA > valueB) {
                    return this.sortOrder === 'asc' ? 1 : -1;
                }
                return 0;
            });

            return sorted;
        },

        // Total number of pages
        totalPages() {
            return Math.ceil(this.filteredAndSortedTransactions.length / this.itemsPerPage);
        },

        // Paginated transactions
        paginatedTransactions() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.filteredAndSortedTransactions.slice(start, end);
        },

        // Page numbers for pagination controls
        pageNumbers() {
            const totalPages = this.totalPages;
            let startPage = Math.max(1, this.currentPage - 10);
            let endPage = Math.min(totalPages, startPage + 19);
            return Array.from({length: endPage - startPage + 1}, (_, index) => startPage + index);
        },
    },
    methods: {
        validateCreateForm() {

            // Validate each field and set the appropriate error message
            if (!this.newTransaction.reference) {
                this.error.reference = 'Reference is required';
                return false;
            }
            if (!this.newTransaction.amount) {
                this.error.amount = 'Amount is required';
                return false;
            } else if (isNaN(this.newTransaction.amount)) {
                this.error.amount = 'Amount must be a number';
                return false;
            }

            if (!this.newTransaction.transaction_date) {
                this.error.transaction_date = 'Transaction date is required';
                return false;
            }

            if (!this.newTransaction.value_date) {
                this.error.value_date = 'Value date is required';
                return false;
            }

            if (!this.newTransaction.payment_type) {
                this.error.payment_type = 'Payment type is required';
                return false;
            }

            return true;
        },
        validateUpdateForm() {
            // Validate each field and set the appropriate error message
            if (!this.currentTransaction.reference) {
                this.error.reference = 'Reference is required';
                return false;
            }
            if (!this.currentTransaction.amount) {
                this.error.amount = 'Amount is required';
                return false;
            } else if (isNaN(this.currentTransaction.amount)) {
                this.error.amount = 'Amount must be a number';
                return false;
            }
            if (!this.currentTransaction.transaction_date) {
                this.error.transaction_date = 'Transaction date is required';
                return false;
            }
            if (!this.currentTransaction.value_date) {
                this.error.value_date = 'Value date is required';
                return false;
            }
            if (!this.currentTransaction.payment_type) {
                this.error.payment_type = 'Payment type is required';
                return false;
            }
            // If no errors, return true (form is valid)
            return true;
        },
        capitalizeFirstLetter(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        },
        toggleTransactionSelection(transaction) {
            // Check if the transaction ID is already in the tickedTransactions array
            const index = this.tickedTransactions.indexOf(transaction.id);

            if (index === -1) {
                // If the ID is not in the array, add it
                this.tickedTransactions.push(transaction.id);
            } else {
                // If the ID is already in the array, remove it
                this.tickedTransactions.splice(index, 1);
            }

            // Log the updated tickedTransactions array to the console
            console.log(this.tickedTransactions);
        },
        async sendToBank() {
            // Send the selected transactions to the Laravel API
            try {
                const token = localStorage.getItem('authToken'); // Or retrieve from Vuex/Pinia
                let response;

                const config = {
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}` // Add token to headers
                    }
                };
                response = await axios.post('/api/send-to-bank', {
                    transaction_ids: this.tickedTransactions
                });

                if (response.data.success) {
                    Swal.fire('Confirmed!', 'Transactions successfully sent to bank.', 'success');
                    // You might want to clear the tickedTransactions array
                    this.tickedTransactions = [];
                    this.fetchTransactions();
                }
            } catch (error) {
                console.error('Error sending to bank:', error);
                alert('An error occurred while sending transactions.');
            }
        },
        async fetchTransactions() {
            try {
                const token = localStorage.getItem('authToken'); // Or retrieve from Vuex/Pinia
                let response;

                const config = {
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}` // Add token to headers
                    }
                };
                response = await axios.get('/api/transactions', config);
                this.transactions = response.data;
            } catch (error) {
                console.error("Error fetching transactions:", error);
                Swal.fire("Error", "Failed to fetch transactions.", "error");
            }
        },
        editTransaction(transaction) {
            this.currentTransaction = {...transaction};
        },
        async saveTransaction() {


            try {
                const token = localStorage.getItem('authToken'); // Or retrieve from Vuex/Pinia
                let response;

                const config = {
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    }
                };

                if (this.currentTransaction.id && this.validateUpdateForm()) {
                    this.isLoading=true;
                    // **Update Existing Transaction**
                    response = await axios.put(`/api/transactions/${this.currentTransaction.id}`, {
                        reference: this.currentTransaction.reference,
                        amount: this.currentTransaction.amount,
                        transaction_date: this.currentTransaction.transaction_date,
                        value_date: this.currentTransaction.value_date,
                        payment_type: this.currentTransaction.payment_type
                    }, config);
                } else if (!this.currentTransaction.id && this.validateCreateForm()) {
                    this.isLoading=true;
                    // **Create New Transaction**
                    response = await axios.post('/api/transactions', {
                        reference: this.newTransaction.reference,
                        amount: this.newTransaction.amount,
                        transaction_date: this.newTransaction.transaction_date,
                        value_date: this.newTransaction.value_date,
                        payment_type: this.newTransaction.payment_type
                    }, config);
                }

                if (response.status !== 200) {
                    this.isLoading=false;
                    Swal.fire({
                        text: 'Failed to save transaction',
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok",
                        heightAuto: false,
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    }).then(async () => {
                        this.currentTransaction = {};
                        this.newTransaction = {};
                        await this.fetchTransactions();
                    });
                } else {
                    this.isLoading=false;
                    console.log("in there")
                    Swal.fire({
                        text: 'Transaction saved successfully.',
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: "Ok",
                        heightAuto: false,
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    }).then(async () => {
                        this.currentTransaction = {};
                        this.newTransaction = {};
                        await this.fetchTransactions();
                    });

                }


            } catch (error) {
                console.error('Error saving transaction:', error);
            }
        },

        confirmDelete(transaction) {
            Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to delete transaction ID ${transaction.id}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
            }).then(async (result) => {
                if (result.isConfirmed) {
                    await this.deleteTransaction(transaction);
                }
            });
        },

        async deleteTransaction(transaction) {
            try {
                const token = localStorage.getItem('authToken'); // Or retrieve from Vuex/Pinia
                let response;

                const config = {
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}` // Add token to headers
                    }
                };
                response = await axios.delete(`/api/transactions/${transaction.id}`, config);
                if (response.status === 200) {
                    Swal.fire("Deleted!", "Transaction has been deleted.", "success");
                    this.fetchTransactions()
                }
            } catch (error) {
                console.error("Error deleting transaction:", error);
                Swal.fire("Error", "Failed to delete transaction.", "error");
            }
        },

        changePage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
        },
        openAddTransactionModal() {
            this.currentTransaction = {id: null, amount: '', description: '', date: ''}; // Initialize new transaction
        },
        resetFilters() {
            this.filters = {
                reference: '',
                amount: '',
                transaction_date: '',
                value_date: '',
                payment_type: ''
            };
        },
        sendTransactionsToBank() {
            Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to send the selected transactions to the bank ?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Send!',
                cancelButtonText: 'No, cancel!',
            }).then(async (result) => {
                if (result.isConfirmed) {
                    await this.sendToBank();
                }
            });
        },

        sortTable(column) {
            if (this.sortColumn === column) {
                // If the same column is clicked, toggle the order
                this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                // If a different column is clicked, set the column and default to ascending order
                this.sortColumn = column;
                this.sortOrder = 'asc';
            }
        },
        // Get the correct icon based on sorting order
        getSortIcon(column) {
            if (this.sortColumn === column) {
                return this.sortOrder === 'asc' ? 'fas fa-arrow-up' : 'fas fa-arrow-down';
            }
            return 'fas fa-sort'; // Default icon when no sorting is applied
        },
        // Compare two dates (transaction_date or value_date)
        compareDates(dateA, dateB) {
            const timestampA = new Date(dateA).getTime();
            const timestampB = new Date(dateB).getTime();

            if (timestampA < timestampB) {
                return this.sortOrder === 'asc' ? -1 : 1;
            } else if (timestampA > timestampB) {
                return this.sortOrder === 'asc' ? 1 : -1;
            }
            return 0;
        },
        formatDate(date) {
            if (!date) return "Invalid Date"; // Handle empty or null values

            try {
                const parsedDate = parseISO(date); // Ensure proper date parsing
                return format(parsedDate, "dd-MMM-yyyy"); // Format correctly
            } catch (error) {
                return "Invalid Date"; // Handle errors gracefully
            }
        }
    },
    mounted() {
        this.fetchTransactions();
    }
};
</script>


<style scoped>
/* Style for validation */
.has-error input, .has-error textarea {
    border-color: red;
}

.has-error label {
    color: red;
}
</style>
