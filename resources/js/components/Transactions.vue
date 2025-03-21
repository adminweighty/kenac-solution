<template>
    <div>

        <!-- Dropdown for Add Transaction -->
        <div class="dropdown mb-4">
            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Add Transaction
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item" href="#" @click="openAddTransactionModal">Add New Transaction</a>
            </div>
        </div>
        <!-- Filters Section -->
        <div class="filters d-flex align-items-center">
            <input type="text" v-model="filters.reference" placeholder="Filter by Reference" class="form-control mb-2 mr-2" />
            <input type="number" v-model="filters.amount" placeholder="Filter by Amount" class="form-control mb-2 mr-2" />
            <input type="date" v-model="filters.transactionDate" placeholder="Filter by Transaction Date" class="form-control mb-2 mr-2" />
            <input type="date" v-model="filters.valueDate" placeholder="Filter by Value Date" class="form-control mb-2 mr-2" />

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
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th @click="sortTable('id')">ID <i :class="getSortIcon('id')"></i></th>
                        <th @click="sortTable('reference')">Reference <i :class="getSortIcon('reference')"></i></th>
                        <th @click="sortTable('amount')">Amount <i :class="getSortIcon('amount')"></i></th>
                        <th @click="sortTable('transactionDate')">Transaction Date <i :class="getSortIcon('transactionDate')"></i></th>
                        <th @click="sortTable('valueDate')">Value Date <i :class="getSortIcon('valueDate')"></i></th>
                        <th>Send to Bank</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="transaction in paginatedTransactions" :key="transaction.id">
                        <td>{{ transaction.id }}</td>
                        <td>{{ transaction.reference }}</td>
                        <td>{{ transaction.amount }}</td>
                        <td>{{ formatDate(transaction.transactionDate) }}</td>
                        <td>{{ formatDate(transaction.valueDate) }}</td>
                        <td>
                            <input type="checkbox" v-model="transaction.sendToBank" />
                        </td>
                        <td>
                            <button class="btn btn-warning btn-sm" @click="editTransaction(transaction)">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="btn btn-danger btn-sm button-spacing" @click="confirmDelete(transaction)">
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
            <button class="btn btn-primary btn-sm" :disabled="currentPage === 1" @click="changePage(currentPage - 1)">Previous</button>

            <!-- Pagination Numbers -->
            <button
                v-for="page in pageNumbers"
                :key="page"
                class="btn btn-light btn-sm"
                :class="{ active: currentPage === page }"
                @click="changePage(page)">
                {{ page }}
            </button>

            <button class="btn btn-primary btn-sm" :disabled="currentPage === totalPages" @click="changePage(currentPage + 1)">Next</button>
        </div>


        <!-- Add Transaction Modal -->
        <div class="modal fade" id="addTransactionModal" tabindex="-1" role="dialog" aria-labelledby="addTransactionModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addTransactionModalLabel">Add New Transaction</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Form Inputs for Adding Transaction -->
                        <div class="form-group">
                            <label for="reference">Reference</label>
                            <input type="text" class="form-control" id="reference" v-model="newTransaction.reference" />
                        </div>
                        <div class="form-group">
                            <label for="amount">Amount</label>
                            <input type="number" class="form-control" id="amount" v-model="newTransaction.amount" />
                        </div>
                        <div class="form-group">
                            <label for="transactionDate">Transaction Date</label>
                            <input type="date" class="form-control" id="transactionDate" v-model="newTransaction.transactionDate" />
                        </div>
                        <div class="form-group">
                            <label for="valueDate">Value Date</label>
                            <input type="date" class="form-control" id="valueDate" v-model="newTransaction.valueDate" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" @click="addTransaction">Save Transaction</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Edit Transaction Modal -->
        <div class="modal fade" id="editTransactionModal" tabindex="-1" role="dialog" aria-labelledby="editTransactionModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTransactionModalLabel">Edit Transaction</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Form Inputs for Editing -->
                        <div class="form-group" :class="{'has-error': !currentTransaction.reference}">
                            <label for="reference">Reference</label>
                            <input type="text" class="form-control" id="reference" v-model="currentTransaction.reference" />
                            <small v-if="!currentTransaction.reference" class="text-danger">Reference is required.</small>
                        </div>
                        <div class="form-group" :class="{'has-error': !currentTransaction.amount}">
                            <label for="amount">Amount</label>
                            <input type="number" class="form-control" id="amount" v-model="currentTransaction.amount" />
                            <small v-if="!currentTransaction.amount" class="text-danger">Amount is required.</small>
                        </div>
                        <div class="form-group" :class="{'has-error': !currentTransaction.transactionDate}">
                            <label for="transactionDate">Transaction Date</label>
                            <input type="date" class="form-control" id="transactionDate" v-model="currentTransaction.transactionDate" />
                            <small v-if="!currentTransaction.transactionDate" class="text-danger">Transaction date is required.</small>
                        </div>
                        <div class="form-group" :class="{'has-error': !currentTransaction.valueDate}">
                            <label for="valueDate">Value Date</label>
                            <input type="date" class="form-control" id="valueDate" v-model="currentTransaction.valueDate" />
                            <small v-if="!currentTransaction.valueDate" class="text-danger">Value date is required.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" @click="saveTransaction" :disabled="isFormInvalid">Save changes</button>
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
</style>
<script>
import Swal from 'sweetalert2';

export default {
    data() {
        return {
            transactions: [
                { id: 1, reference: 'TXN001', amount: 150, transactionDate: '2025-03-01', valueDate: '2025-03-02', sendToBank: false },
                { id: 2, reference: 'TXN002', amount: 200, transactionDate: '2025-03-02', valueDate: '2025-03-03', sendToBank: true },
                { id: 3, reference: 'TXN003', amount: 300, transactionDate: '2025-03-03', valueDate: '2025-03-04', sendToBank: false },
                { id: 4, reference: 'TXN004', amount: 250, transactionDate: '2025-03-04', valueDate: '2025-03-05', sendToBank: true },
                { id: 5, reference: 'TXN005', amount: 500, transactionDate: '2025-03-05', valueDate: '2025-03-06', sendToBank: false },
                { id: 6, reference: 'TXN006', amount: 450, transactionDate: '2025-03-06', valueDate: '2025-03-07', sendToBank: true },
                { id: 7, reference: 'TXN007', amount: 650, transactionDate: '2025-03-07', valueDate: '2025-03-08', sendToBank: false },
                { id: 8, reference: 'TXN008', amount: 700, transactionDate: '2025-03-08', valueDate: '2025-03-09', sendToBank: true },
                { id: 9, reference: 'TXN009', amount: 900, transactionDate: '2025-03-09', valueDate: '2025-03-10', sendToBank: false },
                { id: 10, reference: 'TXN010', amount: 1200, transactionDate: '2025-03-10', valueDate: '2025-03-11', sendToBank: true },
                { id: 11, reference: 'TXN011', amount: 1300, transactionDate: '2025-03-11', valueDate: '2025-03-12', sendToBank: false },
                { id: 12, reference: 'TXN012', amount: 1100, transactionDate: '2025-03-12', valueDate: '2025-03-13', sendToBank: true },
                { id: 13, reference: 'TXN013', amount: 950, transactionDate: '2025-03-13', valueDate: '2025-03-14', sendToBank: false },
                { id: 14, reference: 'TXN014', amount: 850, transactionDate: '2025-03-14', valueDate: '2025-03-15', sendToBank: true },
                { id: 15, reference: 'TXN015', amount: 1100, transactionDate: '2025-03-15', valueDate: '2025-03-16', sendToBank: false },
                { id: 16, reference: 'TXN016', amount: 950, transactionDate: '2025-03-16', valueDate: '2025-03-17', sendToBank: true },
                { id: 17, reference: 'TXN017', amount: 1250, transactionDate: '2025-03-17', valueDate: '2025-03-18', sendToBank: false },
                { id: 18, reference: 'TXN018', amount: 1350, transactionDate: '2025-03-18', valueDate: '2025-03-19', sendToBank: true },
                { id: 19, reference: 'TXN019', amount: 1400, transactionDate: '2025-03-19', valueDate: '2025-03-20', sendToBank: false },
                { id: 20, reference: 'TXN020', amount: 1550, transactionDate: '2025-03-20', valueDate: '2025-03-21', sendToBank: true },
                { id: 21, reference: 'TXN021', amount: 1600, transactionDate: '2025-03-21', valueDate: '2025-03-22', sendToBank: false },
                { id: 22, reference: 'TXN022', amount: 1700, transactionDate: '2025-03-22', valueDate: '2025-03-23', sendToBank: true },
                { id: 23, reference: 'TXN023', amount: 1800, transactionDate: '2025-03-23', valueDate: '2025-03-24', sendToBank: false },
                { id: 24, reference: 'TXN024', amount: 1900, transactionDate: '2025-03-24', valueDate: '2025-03-25', sendToBank: true },
                { id: 25, reference: 'TXN025', amount: 2000, transactionDate: '2025-03-25', valueDate: '2025-03-26', sendToBank: false },
                { id: 26, reference: 'TXN026', amount: 2100, transactionDate: '2025-03-26', valueDate: '2025-03-27', sendToBank: true },
                { id: 27, reference: 'TXN027', amount: 2200, transactionDate: '2025-03-27', valueDate: '2025-03-28', sendToBank: false },
                { id: 28, reference: 'TXN028', amount: 2300, transactionDate: '2025-03-28', valueDate: '2025-03-29', sendToBank: true },
                { id: 29, reference: 'TXN029', amount: 2400, transactionDate: '2025-03-29', valueDate: '2025-03-30', sendToBank: false },
                { id: 30, reference: 'TXN030', amount: 2500, transactionDate: '2025-03-30', valueDate: '2025-03-31', sendToBank: true },
            ],
            currentPage: 1,
            itemsPerPage: 10,
            filters: {
                reference: '',
                amount: '',
                transactionDate: '',
                valueDate: ''
            },
            newTransaction: {
                reference: '',
                amount: '',
                transactionDate: '',
                valueDate: ''
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
                    (!this.filters.transactionDate || transaction.transactionDate.includes(this.filters.transactionDate)) &&
                    (!this.filters.valueDate || transaction.valueDate.includes(this.filters.valueDate))
                );
            });

            // Apply sorting to the filtered transactions
            const sorted = filtered.sort((a, b) => {
                const valueA = a[this.sortColumn];
                const valueB = b[this.sortColumn];

                if (this.sortColumn === 'transactionDate' || this.sortColumn === 'valueDate') {
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
            return Array.from({ length: endPage - startPage + 1 }, (_, index) => startPage + index);
        },
    },
    methods: {
        editTransaction(transaction) {
            this.currentTransaction = { ...transaction };
            $('#editTransactionModal').modal('show');
        },

        saveTransaction() {
            const index = this.transactions.findIndex(t => t.id === this.currentTransaction.id);
            if (index !== -1) {
                this.transactions[index] = { ...this.currentTransaction };
            }
            $('#editTransactionModal').modal('hide');
        },

        confirmDelete(transaction) {
            Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to delete transaction ID ${transaction.id}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deleteTransaction(transaction);
                }
            });
        },

        deleteTransaction(transaction) {
            const index = this.transactions.findIndex(t => t.id === transaction.id);
            if (index !== -1) {
                this.transactions.splice(index, 1);
            }
        },

        changePage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
        },
        openAddTransactionModal() {
            // Open the modal for adding a new transaction
            $('#addTransactionModal').modal('show');
        },
        resetFilters() {
            this.filters = {
                reference: '',
                amount: '',
                transactionDate: '',
                valueDate: ''
            };
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
        // Compare two dates (transactionDate or valueDate)
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
            return new Date(date).toLocaleDateString('en-GB');
        }
    },
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
