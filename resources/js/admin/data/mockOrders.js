export const mockOrders = [
    {
        id: "10254",
        customer: "Johnathan Wick",
        orderDate: "2023-10-24",
        itemsCount: 3,
        orderStatus: "delivered",
        amount: 1540.0,
        paidAmount: 1540.0,
        paymentStatus: "paid",
        trackingId: "TRK-99281-X",
        courier: "DHL Express",
        discount: 0,
        items: [
            { id: "i1", name: "Premium Wedding Card", price: 500.0, quantity: 3, image: null }
        ],
        customerDetails: {
            name: "Johnathan Wick",
            email: "wick@continental.com",
            mobile: "+1 (555) 000-0001",
            addressLine1: "135 West 55th Street",
            city: "New York",
            state: "NY",
            pincode: "10019",
            country: "USA"
        },
        timeline: [
            { status: "Order Placed", date: "Oct 24", time: "10:00 AM", completed: true, description: "Order received via Online Portal" },
            { status: "Processing", date: "Oct 24", time: "02:30 PM", completed: true, description: "Payment verified and items prepared" },
            { status: "Shipped", date: "Oct 25", time: "09:00 AM", completed: true, description: "Dispatched via DHL" },
            { status: "Delivered", date: "Oct 27", time: "04:15 PM", completed: true, description: "Package delivered to customer" }
        ]
    },
    {
        id: "10255",
        customer: "Sara Connor",
        orderDate: "2023-10-25",
        itemsCount: 1,
        orderStatus: "pending",
        amount: 120.5,
        paidAmount: 0.0,
        paymentStatus: "unpaid",
        trackingId: null,
        courier: null,
        discount: 5,
        items: [
            { id: "i2", name: "Birthday Invitation Box", price: 125.5, quantity: 1, image: null }
        ],
        customerDetails: {
            name: "Sara Connor",
            email: "sara@resistance.net",
            mobile: "+1 (555) 999-2029",
            addressLine1: "Cyberdyne Way 101",
            city: "Los Angeles",
            state: "CA",
            pincode: "90001",
            country: "USA"
        },
        timeline: [
            { status: "Order Placed", date: "Oct 25", time: "11:20 AM", completed: true, description: "Awaiting payment confirmation" }
        ]
    },
    {
        id: "10256",
        customer: "Tony Stark",
        orderDate: "2023-10-26",
        itemsCount: 5,
        orderStatus: "processing",
        amount: 12450.0,
        paidAmount: 5000.0,
        paymentStatus: "partial",
        trackingId: "TRK-AVNGR-1",
        courier: "Stark Logistics",
        discount: 50,
        items: [
            { id: "i3", name: "Holographic Gala Card", price: 2500.0, quantity: 5, image: null }
        ],
        customerDetails: {
            name: "Tony Stark",
            email: "tony@stark.com",
            mobile: "+1 (555) 300-4000",
            addressLine1: "Stark Tower, 89 E 42nd St",
            city: "New York",
            state: "NY",
            pincode: "10017",
            country: "USA"
        },
        timeline: [
            { status: "Order Placed", date: "Oct 26", time: "09:00 AM", completed: true, description: "Custom design request received" },
            { status: "Processing", date: "Oct 26", time: "01:00 PM", completed: true, description: "Material sourcing in progress" }
        ]
    }
];
