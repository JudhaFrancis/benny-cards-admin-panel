<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="handleClose" class="relative z-50">
      <!-- Backdrop -->
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95 translateY(20px)"
            enter-to="opacity-100 scale-100 translateY(0)"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100 translateY(0)"
            leave-to="opacity-0 scale-95 translateY(20px)"
          >
            <DialogPanel
              class="w-full max-w-6xl transform overflow-hidden rounded-[2.5rem] bg-white shadow-2xl transition-all border border-gray-100 flex flex-col"
              :style="{ maxHeight: 'calc(100vh - 4rem)' }"
            >
              <!-- Sticky Header -->
              <div
                class="relative bg-primary text-white overflow-hidden shrink-0 sticky top-0 z-10"
              >
                <!-- Decorative Background -->
                <div
                  class="absolute inset-0 bg-gradient-to-r from-primary/20 to-transparent"
                ></div>
                <div
                  class="absolute -right-20 -top-20 w-96 h-96 bg-primary/10 rounded-full blur-3xl"
                ></div>

                <div
                  class="relative px-8 py-8 flex items-center justify-between"
                >
                  <div class="flex items-center gap-5">
                    <div
                      class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 flex items-center justify-center shadow-inner"
                    >
                      <PackageIcon class="h-7 w-7 text-white" />
                    </div>
                    <div>
                      <DialogTitle
                        as="h3"
                        class="text-2xl font-bold tracking-tight text-white mb-1"
                      >
                        Create New Order
                      </DialogTitle>
                      <p class="text-sm text-white/80">
                        Add a new order to your system with customer and product details.
                      </p>
                    </div>
                  </div>
                  <button
                    @click="handleClose"
                    class="p-3 rounded-xl bg-white/5 hover:bg-white/10 text-white/80 hover:text-white transition-all border border-white/5 active:scale-95"
                  >
                    <XIcon class="h-6 w-6" />
                  </button>
                </div>
              </div>

              <!-- Main Content: Two Column Layout -->
              <div class="flex-1 overflow-hidden flex flex-col md:flex-row">
                <!-- Left Side: Main Form (Scrollable) -->
                <div class="flex-1 overflow-y-auto p-8 pb-32 custom-scrollbar">
                  <div class="space-y-10">
                    <!-- Customer Details Section -->
                    <section>
                      <h4
                        class="text-sm font-bold text-gray-700 mb-6 flex items-center gap-2 ml-1"
                      >
                        <UserIcon class="h-4 w-4 text-primary" /> Customer
                        Information
                      </h4>
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                          <label
                            class="block text-sm font-bold text-gray-700 mb-2.5 ml-1"
                            >Full Name<span class="text-rose-500"
                              >*</span
                            ></label
                          >
                          <input
                            v-model="form.customer.name"
                            type="text"
                            class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-gray-700 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all font-semibold"
                            placeholder="John Doe"
                            required
                          />
                        </div>
                        <div>
                          <label
                            class="block text-sm font-bold text-gray-700 mb-2.5 ml-1"
                            >Email Address</label
                          >
                          <input
                            v-model="form.customer.email"
                            type="email"
                            class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-gray-700 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all font-semibold"
                            placeholder="john@example.com"
                          />
                        </div>
                        <div>
                          <label
                            class="block text-sm font-bold text-gray-700 mb-2.5 ml-1"
                            >Phone Number<span class="text-rose-500"
                              >*</span
                            ></label
                          >
                          <input
                            v-model="form.customer.phone"
                            type="tel"
                            class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-gray-700 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all font-semibold"
                            placeholder="+1 234 567 890"
                            required
                          />
                        </div>
                        <!-- Primary Address Header -->
                        <div
                          class="md:col-span-2 pt-4 border-t border-gray-100 mt-2"
                        >
                          <h5
                            class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4 flex items-center gap-2"
                          >
                            <span class="w-8 h-[1px] bg-gray-100"></span>
                            Primary Address
                          </h5>
                        </div>

                        <div class="md:col-span-2">
                          <label
                            class="block text-sm font-bold text-gray-700 mb-2.5 ml-1"
                            >Street Address<span class="text-rose-500"
                              >*</span
                            ></label
                          >
                          <input
                            v-model="form.customer.address_1"
                            type="text"
                            class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-gray-700 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all font-semibold"
                            placeholder="Primary Street Address"
                            required
                          />
                        </div>

                        <div>
                          <label
                            class="block text-sm font-bold text-gray-700 mb-2.5 ml-1"
                            >City<span class="text-rose-500">*</span></label
                          >
                          <input
                            v-model="form.customer.city_1"
                            type="text"
                            class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-gray-700 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all font-semibold"
                            placeholder="City"
                            required
                          />
                        </div>

                        <div>
                          <label
                            class="block text-sm font-bold text-gray-700 mb-2.5 ml-1"
                            >State / Province<span class="text-rose-500"
                              >*</span
                            ></label
                          >
                          <input
                            v-model="form.customer.state_1"
                            type="text"
                            class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-gray-700 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all font-semibold"
                            placeholder="State"
                            required
                          />
                        </div>

                        <div>
                          <label
                            class="block text-sm font-bold text-gray-700 mb-2.5 ml-1"
                            >Country<span class="text-rose-500">*</span></label
                          >
                          <input
                            v-model="form.customer.country"
                            type="text"
                            class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-gray-700 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all font-semibold"
                            placeholder="Country"
                            required
                          />
                        </div>

                        <div>
                          <label
                            class="block text-sm font-bold text-gray-700 mb-2.5 ml-1"
                            >Post Code<span class="text-rose-500"
                              >*</span
                            ></label
                          >
                          <input
                            v-model="form.customer.post_code_1"
                            type="text"
                            class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-gray-700 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all font-semibold"
                            placeholder="Post Code"
                            required
                          />
                        </div>

                        <!-- Extra Address Header -->
                        <div
                          class="md:col-span-2 pt-6 border-t border-gray-100 mt-2"
                        >
                          <h5
                            class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4 flex items-center gap-2"
                          >
                            <span class="w-8 h-[1px] bg-gray-100"></span>
                            Extra / Secondary Address
                          </h5>
                        </div>

                        <div class="md:col-span-2">
                          <label
                            class="block text-sm font-bold text-gray-700 mb-2.5 ml-1"
                            >Street Address (Extra)</label
                          >
                          <input
                            v-model="form.customer.address_2"
                            type="text"
                            class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-gray-700 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all font-semibold"
                            placeholder="Secondary Street Address"
                          />
                        </div>

                        <div>
                          <label
                            class="block text-sm font-bold text-gray-700 mb-2.5 ml-1"
                            >City (Extra)</label
                          >
                          <input
                            v-model="form.customer.city_2"
                            type="text"
                            class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-gray-700 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all font-semibold"
                            placeholder="City 2"
                          />
                        </div>

                        <div>
                          <label
                            class="block text-sm font-bold text-gray-700 mb-2.5 ml-1"
                            >State (Extra)</label
                          >
                          <input
                            v-model="form.customer.state_2"
                            type="text"
                            class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-gray-700 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all font-semibold"
                            placeholder="State 2"
                          />
                        </div>

                        <div class="md:col-span-2">
                          <label
                            class="block text-sm font-bold text-gray-700 mb-2.5 ml-1"
                            >Post Code (Extra)</label
                          >
                          <input
                            v-model="form.customer.post_code_2"
                            type="text"
                            class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-gray-700 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all font-semibold"
                            placeholder="Post Code 2"
                          />
                        </div>

                        <!-- Remarks Section -->
                        <div
                          class="md:col-span-2 pt-6 border-t border-gray-100 mt-2"
                        >
                          <h5
                            class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4 flex items-center gap-2"
                          >
                            <span class="w-8 h-[1px] bg-gray-100"></span>
                            Order Remarks
                          </h5>
                        </div>

                        <div class="md:col-span-2">
                          <label
                            class="block text-sm font-bold text-gray-700 mb-2.5 ml-1"
                            >Additional Notes (Optional)</label
                          >
                          <textarea
                            v-model="form.remarks"
                            rows="3"
                            class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-gray-700 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all font-semibold resize-none"
                            placeholder="Add any special instructions or notes for this order..."
                          ></textarea>
                        </div>
                      </div>
                    </section>

                    <hr class="border-gray-100" />

                    <!-- Order Items Section -->
                    <section>
                      <div class="flex items-center justify-between mb-6 px-1">
                        <h4
                          class="text-sm font-bold text-gray-700 flex items-center gap-2"
                        >
                          <PlusIcon class="h-4 w-4 text-primary" /> Select
                          Products
                        </h4>
                        <button
                          type="button"
                          @click="addItem"
                          class="px-4 py-2 bg-primary/5 text-primary rounded-2xl font-semibold hover:bg-primary/10 transition-all flex items-center gap-2 active:scale-95 text-sm"
                        >
                          <PlusIcon class="h-4 w-4" /> Add Item
                        </button>
                      </div>

                      <div class="space-y-4">
                        <div
                          v-for="(item, index) in form.items"
                          :key="index"
                          class="grid grid-cols-12 gap-4 items-center p-6 rounded-[2rem] bg-gray-50/50 border border-gray-100 shadow-sm relative group"
                        >
                          <!-- Product info -->
                          <div class="col-span-12 md:col-span-6">
                            <label
                              class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2"
                              >Product</label
                            >
                            <Combobox
                              v-model="item.product_id"
                              @update:modelValue="handleProductChange(index)"
                            >
                              <div class="relative mt-1">
                                <div
                                  class="relative w-full cursor-default overflow-hidden rounded-xl bg-white border border-gray-200 text-left focus-within:ring-2 focus-within:ring-primary/20 focus-within:border-primary transition-all font-semibold"
                                >
                                  <ComboboxInput
                                    class="w-full border-none py-3 pl-10 pr-10 text-sm leading-5 text-gray-900 focus:ring-0 outline-none bg-transparent"
                                    :displayValue="
                                      (id) =>
                                        Array.isArray(products)
                                          ? products.find((p) => p.id === id)
                                              ?.title || ''
                                          : ''
                                    "
                                    @change="
                                      item.search_query = $event.target.value
                                    "
                                    placeholder="Search product..."
                                  />
                                  <div
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"
                                  >
                                    <SearchIcon
                                      class="h-4 w-4 text-gray-400"
                                      aria-hidden="true"
                                    />
                                  </div>
                                  <ComboboxButton
                                    class="absolute inset-y-0 right-0 flex items-center pr-2"
                                  >
                                    <ChevronDownIcon
                                      class="h-4 w-4 text-gray-400"
                                      aria-hidden="true"
                                    />
                                  </ComboboxButton>
                                </div>
                                <transition
                                  leave-active-class="transition ease-in duration-100"
                                  leave-from-class="opacity-100"
                                  leave-to-class="opacity-0"
                                  @after-leave="item.search_query = ''"
                                >
                                  <ComboboxOptions
                                    class="absolute z-50 mt-2 max-h-72 w-full overflow-auto rounded-[1.5rem] bg-white p-2 text-base shadow-[0_20px_50px_rgba(0,0,0,0.15)] ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm custom-scrollbar"
                                  >
                                    <div
                                      v-if="
                                        getFilteredProducts(item.search_query)
                                          .length === 0 &&
                                        item.search_query !== ''
                                      "
                                      class="relative cursor-default select-none py-10 px-4 text-gray-400 text-center"
                                    >
                                      <PackageIcon
                                        class="h-8 w-8 mx-auto mb-2 opacity-20"
                                      />
                                      <p class="font-medium">
                                        No products found
                                      </p>
                                    </div>

                                    <ComboboxOption
                                      v-for="product in getFilteredProducts(
                                        item.search_query,
                                      )"
                                      as="template"
                                      :key="product.id"
                                      :value="product.id"
                                      v-slot="{ selected, active }"
                                    >
                                      <li
                                        class="relative cursor-default select-none py-2.5 pl-3 pr-4 transition-all rounded-2xl mb-1 last:mb-0"
                                        :class="{
                                          'bg-primary/5 text-primary': active,
                                          'text-gray-900': !active,
                                        }"
                                      >
                                        <div class="flex items-center gap-4">
                                          <!-- Product Thumbnail -->
                                          <div
                                            class="w-12 h-12 rounded-xl bg-gray-50 border border-gray-100 overflow-hidden flex-shrink-0 flex items-center justify-center"
                                          >
                                            <img
                                              v-if="product.image"
                                              :src="
                                                getImageSource(product.image)
                                              "
                                              class="w-full h-full object-cover"
                                              @error="handleImageError"
                                            />
                                            <PackageIcon
                                              v-else
                                              class="h-5 w-5 text-gray-300"
                                            />
                                          </div>

                                          <div class="flex-1 min-w-0">
                                            <p
                                              class="text-sm font-bold truncate leading-tight"
                                              :class="{
                                                'text-primary': active,
                                                'text-gray-800': !active,
                                              }"
                                            >
                                              {{ product.title }}
                                            </p>
                                            <p
                                              class="text-[11px] font-bold text-gray-400 mt-0.5 tracking-tight uppercase"
                                            >
                                              SKU: {{ product.sku || "N/A" }}
                                            </p>
                                          </div>

                                          <div class="text-right">
                                            <p
                                              class="text-sm font-black"
                                              :class="
                                                active
                                                  ? 'text-primary'
                                                  : 'text-gray-900'
                                              "
                                            >
                                              ${{ product.price }}
                                            </p>
                                            <p
                                              v-if="product.stock !== undefined"
                                              class="text-[10px] font-bold text-gray-400 mt-0.5"
                                            >
                                              {{ product.stock }} in stock
                                            </p>
                                          </div>
                                        </div>

                                        <span
                                          v-if="selected"
                                          class="absolute inset-y-0 right-3 flex items-center text-primary"
                                        >
                                          <CheckIcon
                                            class="h-4 w-4"
                                            aria-hidden="true"
                                          />
                                        </span>
                                      </li>
                                    </ComboboxOption>
                                  </ComboboxOptions>
                                </transition>
                              </div>
                            </Combobox>
                          </div>

                          <!-- Qty -->
                          <div class="col-span-4 md:col-span-2">
                            <label
                              class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 text-center"
                              >Qty</label
                            >
                            <input
                              v-model.number="item.quantity"
                              type="number"
                              min="1"
                              class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-gray-700 focus:outline-none focus:border-primary transition-all font-bold text-center text-sm"
                            />
                          </div>

                          <!-- Price -->
                          <div class="col-span-4 md:col-span-2">
                            <label
                              class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 text-center"
                              >Price</label
                            >
                            <div
                              class="py-3 text-sm font-bold text-gray-500 text-center"
                            >
                              ${{ (item.unit_price || 0).toFixed(2) }}
                            </div>
                          </div>

                          <!-- Subtotal -->
                          <div class="col-span-4 md:col-span-2 relative">
                            <label
                              class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 text-center"
                              >Subtotal</label
                            >
                            <div
                              class="py-3 text-sm font-black text-primary text-center"
                            >
                              ${{
                                (item.quantity * item.unit_price || 0).toFixed(
                                  2,
                                )
                              }}
                            </div>

                            <!-- Remove Button -->
                            <button
                              type="button"
                              @click="removeItem(index)"
                              class="absolute -top-2 -right-2 md:top-1/2 md:-right-4 md:-translate-y-1/2 p-2 text-gray-300 hover:text-rose-500 hover:bg-rose-50 rounded-xl transition-all opacity-0 group-hover:opacity-100"
                            >
                              <Trash2Icon class="h-4 w-4" />
                            </button>
                          </div>
                        </div>

                        <!-- Empty State -->
                        <div
                          v-if="form.items.length === 0"
                          class="text-center py-10 px-6 rounded-[2rem] border-2 border-dashed border-gray-100 bg-gray-50/30"
                        >
                          <div
                            class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-400 mx-auto mb-3"
                          >
                            <PackageIcon class="h-6 w-6" />
                          </div>
                          <p class="text-sm text-gray-500 font-medium">
                            Your order basket is empty
                          </p>
                          <p class="text-xs text-gray-400 mt-1">
                            Add products to calculate totals
                          </p>
                        </div>
                      </div>
                    </section>
                  </div>
                </div>

                <!-- Right Side: Order Summary (Sidebar) -->
                <div
                  class="w-full md:w-[380px] bg-gray-50/50 border-l border-gray-100 p-8 flex flex-col"
                >
                  <h4 class="text-sm font-bold text-gray-700 mb-8 ml-1">
                    Order Summary
                  </h4>

                  <div class="flex-1 space-y-6">
                    <!-- Totals Card -->
                    <div
                      class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100 space-y-4"
                    >
                      <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500 font-semibold"
                          >Subtotal</span
                        >
                        <span class="font-bold text-gray-900"
                          >${{ calculateTotal.toFixed(2) }}</span
                        >
                      </div>

                      <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500 font-semibold"
                          >Discount</span
                        >
                        <div class="relative w-32">
                          <span
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold"
                            >$</span
                          >
                          <input
                            v-model.number="form.discount"
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-full pl-7 pr-4 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:outline-none focus:border-primary font-bold text-gray-700 text-right"
                            placeholder="0.00"
                          />
                        </div>
                      </div>

                      <div
                        class="pt-4 border-t border-gray-50 flex justify-between items-center"
                      >
                        <span class="text-gray-900 font-bold">Grand Total</span>
                        <span class="text-2xl font-black text-primary"
                          >${{
                            (calculateTotal - (form.discount || 0)).toFixed(2)
                          }}</span
                        >
                      </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="px-4 space-y-4">
                      <div
                        class="flex items-center gap-3 text-xs text-gray-400"
                      >
                        <div
                          class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"
                        ></div>
                        <span>Order will be created as <b>Pending</b></span>
                      </div>
                      <div
                        class="flex items-center gap-3 text-xs text-gray-400"
                      >
                        <div
                          class="w-1.5 h-1.5 rounded-full bg-green-500"
                        ></div>
                        <span>Instant order number generation</span>
                      </div>
                    </div>
                  </div>

                  <!-- Actions -->
                  <div class="mt-auto pt-10 pb-2 space-y-6">
                    <button
                      @click="handleSubmit"
                      :disabled="loading || form.items.length === 0"
                      class="w-full py-4 bg-primary text-white rounded-[1.5rem] font-bold hover:shadow-xl hover:shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2 active:scale-[0.98]"
                    >
                      <span
                        v-if="loading"
                        class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"
                      ></span>
                      <span v-else>Confirm & Create</span>
                    </button>
                    <button
                      type="button"
                      @click="handleClose"
                      class="w-full py-4 border border-gray-200 text-gray-400 rounded-[1.5rem] font-bold hover:text-gray-600 hover:border-gray-300 hover:bg-gray-50 transition-all text-sm"
                    >
                      Cancel Creation
                    </button>
                  </div>
                </div>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from "vue";
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
  Combobox,
  ComboboxInput,
  ComboboxButton,
  ComboboxOptions,
  ComboboxOption,
} from "@headlessui/vue";
import {
  Search as SearchIcon,
  Check as CheckIcon,
  Package as PackageIcon,
  X as XIcon,
  User as UserIcon,
  ChevronDown as ChevronDownIcon,
  Plus as PlusIcon,
  Trash2 as Trash2Icon,
} from "lucide-vue-next";
import axios from "axios";
import { useToast } from "../../composables/useToast";

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits(["close", "success"]);
const toast = useToast();
const loading = ref(false);
const products = ref([]);

const form = reactive({
  customer: {
    name: "",
    email: "",
    phone: "",
    country: "",
    address_1: "",
    city_1: "",
    state_1: "",
    post_code_1: "",
    address_2: "",
    city_2: "",
    state_2: "",
    post_code_2: "",
  },
  items: [],
  discount: 0,
  remarks: "",
  payment_method: "cash",
  status: "pending",
});

const calculateTotal = computed(() => {
  return form.items.reduce(
    (total, item) => total + (item.quantity * item.unit_price || 0),
    0,
  );
});

const fetchProducts = async () => {
  try {
    const response = await axios.get("/api/v1/products/options");
    if (response.data.success && Array.isArray(response.data.data)) {
      products.value = response.data.data;
    } else if (Array.isArray(response.data)) {
      products.value = response.data;
    } else if (Array.isArray(response.data.data)) {
      products.value = response.data.data;
    } else {
      const listResponse = await axios.get("/api/v1/products?limit=100");
      const listData = listResponse.data;
      if (listData.data && Array.isArray(listData.data.data)) {
        products.value = listData.data.data;
      } else if (Array.isArray(listData.data)) {
        products.value = listData.data;
      } else if (Array.isArray(listData)) {
        products.value = listData;
      } else {
        products.value = [];
      }
    }
  } catch (error) {
    console.error("Failed to fetch products", error);
    toast.error("Failed to load products");
    products.value = [];
  }
};

const getFilteredProducts = (query) => {
  if (!Array.isArray(products.value)) return [];
  if (!query) return products.value;
  const lowercaseQuery = query.toLowerCase();
  return products.value.filter((product) =>
    product.title.toLowerCase().includes(lowercaseQuery),
  );
};

onMounted(() => {
  fetchProducts();
});

const addItem = () => {
  form.items.push({
    product_id: "",
    product_name: "",
    quantity: 1,
    unit_price: 0,
    search_query: "",
  });
};

const removeItem = (index) => {
  form.items.splice(index, 1);
};

const handleProductChange = (index) => {
  const item = form.items[index];
  if (!Array.isArray(products.value)) return;
  const product = products.value.find((p) => p.id === item.product_id);
  if (product) {
    item.product_name = product.title;
    item.unit_price = parseFloat(product.price) || 0;
  }
};

const getImageSource = (path) => {
  if (!path) return "/images/placeholder.webp";
  if (path.startsWith("data:") || path.startsWith("http")) return path;
  return `/${path}`;
};

const handleImageError = (e) => {
  const fallback =
    "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 24 24' fill='none' stroke='%23cbd5e1' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect width='18' height='18' x='3' y='3' rx='2' ry='2'%3E%3C/rect%3E%3Ccircle cx='9' cy='9' r='2'%3E%3C/circle%3E%3Cpath d='m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21'%3E%3C/path%3E%3C/svg%3E";
  if (e.target.src === fallback) return;
  e.target.src = fallback;
};

const resetForm = () => {
  form.items = [];
  form.discount = 0;
  form.remarks = "";
  form.customer = {
    name: "",
    email: "",
    phone: "",
    country: "",
    address_1: "",
    city_1: "",
    state_1: "",
    post_code_1: "",
    address_2: "",
    city_2: "",
    state_2: "",
    post_code_2: "",
  };
};

watch(
  () => props.isOpen,
  (newVal) => {
    if (newVal) {
      resetForm();
    }
  },
);

const handleClose = () => {
  emit("close");
};

const handleSubmit = async () => {
  if (!form.customer.name) {
    toast.error("Customer name is required");
    return;
  }

  if (!form.customer.phone) {
    toast.error("Phone number is required");
    return;
  }

  if (!form.customer.address_1) {
    toast.error("Primary street address is required");
    return;
  }

  if (!form.customer.city_1) {
    toast.error("Primary city is required");
    return;
  }

  if (!form.customer.state_1) {
    toast.error("Primary state is required");
    return;
  }

  if (!form.customer.country) {
    toast.error("Country is required");
    return;
  }

  if (!form.customer.post_code_1) {
    toast.error("Post code is required");
    return;
  }

  loading.value = true;
  try {
    const response = await axios.post("/api/v1/orders", form);
    if (response.data.success) {
      toast.success("Order created successfully");
      emit("success");
      emit("close");
    }
  } catch (error) {
    console.error("Error creating order:", error);
    toast.error(error.response?.data?.message || "Failed to create order");
  } finally {
    loading.value = false;
  }
};
</script>
