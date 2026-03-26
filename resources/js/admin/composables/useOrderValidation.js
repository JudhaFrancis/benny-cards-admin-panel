import { useToast } from "./useToast";

export function useOrderValidation() {
  const { toastError } = useToast();

  const getSectionErrors = (sectionId, order) => {
    const errors = [];
    if (!order) return errors;

    const details = order.client_information?.order_details || {};
    const client = order.client_information?.client_info || {};
    const specs = order.client_information?.card_specs || {};
    const designing = order.designing || {};
    const work = designing.work_assign || {};
    const designPrint = designing.design_print || {};
    const printing = order.printing?.printing_status || {};
    const packaging = order.packaging || {};
    const packLog = packaging.packaging_logistics || {};
    const packStat = packaging.packaging_status || {};
    const dispatch = order.dispatch_delivery || {};
    const delivery = dispatch.delivery_location || {};
    const dispMode = dispatch.dispatch_mode || {};
    const dispDetails = dispatch.dispatch_details || {};

    switch (sectionId) {
      case "order-details":
        // Only fields with * in OrderDetailsSection.vue
        if (!details.order_placed_in) errors.push("Order Placed In");
        if (!details.reference) errors.push("Reference");
        break;

      case "client-info":
        // Only fields with * in ClientInfoSection.vue
        if (!client.name) errors.push("Name");
        if (!client.address) errors.push("Place (Address)");
        if (!client.phone) errors.push("Contact No");
        break;

      case "card-specs":
        // Only fields with * in CardSpecsSection.vue
        if (!specs.type) errors.push("Product Type");
        if (!specs.card_size) errors.push("Card Size");
        if (!specs.quantity) errors.push("Quantity");
        if (!specs.specifications) errors.push("Detailed Specifications");
        break;

      case "work-assign":
        // Only fields with * in WorkAssignSection.vue
        if (!work.assigned_to) errors.push("Assigned To");
        if (!work.assigned_date) errors.push("Assigned Date");
        if (!work.deadline) errors.push("Deadline");
        if (!work.content_by) errors.push("Content By");
        if (!work.completed_by) errors.push("Completed By");
        break;

      case "design-print":
        // Only fields with * in DesignPrintSection.vue
        if (!designPrint.design_outputs) errors.push("Design Outputs");
        if (!designPrint.print_addons) errors.push("Print & Add-ons");
        break;

      case "order-printing":
        // Only fields with * in OrderPrintingSection.vue
        if (!printing.assigned_to) errors.push("Assigned To");
        if (!printing.assigned_date) errors.push("Assigned Date");
        
        // Dynamic validation based on selected card types
        const selectedTypes = order.client_information?.card_specs?.type?.split(',') || [];
        const typeLabels = {
          customize: 'Customize Card',
          semi_customize: 'Semi – Customize Card',
          ready_made: 'Ready Made Card',
          digital_local: 'Digital Local'
        };

        selectedTypes.forEach(type => {
          if (!printing[type + '_sent_to_print_date']) {
            errors.push(`${typeLabels[type] || type}: Sent to Print Date`);
          }
          if (!printing[type + '_delivery_date']) {
            errors.push(`${typeLabels[type] || type}: Delivery Date`);
          }
        });
        break;

      case "packaging-logistics":
        // Only fields with * in PackagingLogisticsSection.vue
        if (!packLog.crafted_by) errors.push("Crafted By");
        if (!packLog.names) errors.push("Names");
        if (!packLog.date) errors.push("Date");
        if (!packLog.qty_cards) errors.push("Qty of Cards");
        if (!packLog.logistics_details) errors.push("Envelope / Ribbon / Tag / Sticker Details");
        break;

      case "packaging-status":
        // Only fields with * in PackagingStatusSection.vue
        if (!packStat.packed_by) errors.push("Packed By");
        break;

      case "delivery-location":
        // Only fields with * in DeliveryLocationSection.vue
        if (!delivery.shops) errors.push("Shop Location");
        if (!delivery.place_name) errors.push("Place Name");
        break;

      case "dispatch-mode":
        // Only fields with * in DispatchModeSection.vue
        if (!dispMode.modes) errors.push("Mode of Dispatch");
        if (!dispMode.date) errors.push("Dispatch Details with Date");
        if (!dispMode.expense && dispMode.expense !== 0) errors.push("Dispatch Expense");
        if (!dispMode.signature_name) errors.push("Signature & Name");
        break;

      case "dispatch-details":
        // Mode-specific mandatory fields (all have * in DispatchDetailsSection.vue)
        if (dispMode.modes === "Bus") {
          if (!dispDetails.bus?.bus_no) errors.push("Bus No");
          if (!dispDetails.bus?.reaching_time) errors.push("Reaching Time");
          if (!dispDetails.bus?.contact_no) errors.push("Contact No");
        } else if (dispMode.modes === "Courier") {
          if (!dispDetails.courier?.name) errors.push("Courier Name");
          if (!dispDetails.courier?.tracking_no) errors.push("Tracking No");
        } else if (dispMode.modes === "Transport") {
          if (!dispDetails.transport?.name) errors.push("Transport Name");
          if (!dispDetails.transport?.lr_number) errors.push("LR Number");
        }
        break;
    }

    return errors;
  };

  const validateStage = (stageRelation, order, sectionMap, trackingSections) => {
    const sectionsInStage = Object.keys(sectionMap).filter(
      (k) => sectionMap[k].relation === stageRelation || k === stageRelation
    );

    let allErrors = [];
    sectionsInStage.forEach((secId) => {
      const errors = getSectionErrors(secId, order);
      if (errors.length > 0) {
        const sectionLabel = trackingSections.find(s => s.id === secId)?.shortLabel || secId;
        allErrors.push(...errors.map((e) => `${sectionLabel}: ${e}`));
      }
    });

    return allErrors;
  };

  const trackingSections = [
    { id: "order-details", label: "Order Details", shortLabel: "Details" },
    { id: "client-info", label: "Client Information", shortLabel: "Client" },
    { id: "card-specs", label: "Card Specifications", shortLabel: "Specs" },
    { id: "work-assign", label: "Work Assign Process", shortLabel: "Assign" },
    { id: "design-print", label: "Design Details", shortLabel: "Design" },
    { id: "order-printing", label: "Order & Printing Status", shortLabel: "Printing" },
    { id: "packaging-logistics", label: "Packaging & Logistics", shortLabel: "Logistics" },
    { id: "packaging-status", label: "Packaging Status", shortLabel: "Packing" },
    { id: "delivery-location", label: "Delivery Location", shortLabel: "Location" },
    { id: "dispatch-mode", label: "Mode of Dispatch", shortLabel: "Dispatch" },
    { id: "dispatch-details", label: "Dispatch Details", shortLabel: "Details" },
    { id: "payment", label: "Payment", shortLabel: "Payment" },
  ];

  const sectionMap = {
    "order-details": { relation: "client_information", key: "order_details" },
    "client-info": { relation: "client_information", key: "client_info" },
    "card-specs": { relation: "client_information", key: "card_specs" },
    "work-assign": { relation: "designing", key: "work_assign" },
    "design-print": { relation: "designing", key: "design_print" },
    "order-printing": { relation: "printing", key: "printing_status" },
    "packaging-logistics": { relation: "packaging", key: "packaging_logistics" },
    "packaging-status": { relation: "packaging", key: "packaging_status" },
    "delivery-location": { relation: "dispatch_delivery", key: "delivery_location" },
    "dispatch-mode": { relation: "dispatch_delivery", key: "dispatch_mode" },
    "dispatch-details": { relation: "dispatch_delivery", key: "dispatch_details" },
    payment: { relation: "payments", isArray: true },
  };

  return {
    getSectionErrors,
    validateStage,
    trackingSections,
    sectionMap
  };
}
