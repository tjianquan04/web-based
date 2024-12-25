let map;

async function initMap() {
  // The location of Kuala Lumpur, Malaysia
  const position = { lat: 3.139, lng: 101.6869 };

  // Request needed libraries.
  //@ts-ignore
  const { Map } = await google.maps.importLibrary("maps");
  const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

  // The map, centered at Kuala Lumpur
  map = new Map(document.getElementById("map"), {
    zoom: 14, // Adjust the zoom level for better view of the city
    center: position,
    mapId: "DEMO_MAP_ID",
  });

  // The marker for Kuala Lumpur
  const marker = new AdvancedMarkerElement({
    map: map,
    position: position,
    title: "Kuala Lumpur",
  });

  // List of badminton shop locations in Kuala Lumpur
  const badmintonShops = [
    { lat: 3.138, lng: 101.698, name: "Boots.Do Badminton Shop" },
    { lat: 3.125, lng: 101.698, name: "Boots.Do Badminton Shop" },
    { lat: 3.149, lng: 101.697, name: "Boots.Do Badminton Shop" },
    { lat: 3.141, lng: 101.692, name: "Boots.Do Badminton Shop" },
    { lat: 3.137, lng: 101.681, name: "Boots.Do Badminton Shop" },
    { lat: 3.136, lng: 101.688, name: "Boots.Do Badminton Shop" },
    { lat: 3.132, lng: 101.703, name: "Boots.Do Badminton Shop" },
    { lat: 3.145, lng: 101.679, name: "Boots.Do Badminton Shop" },
    { lat: 3.130, lng: 101.685, name: "Boots.Do Badminton Shop" },
    { lat: 3.124, lng: 101.690, name: "Boots.Do Badminton Shop" }
  ];

  // Adding markers for each badminton shop
  badmintonShops.forEach(shop => {
    new AdvancedMarkerElement({
      map: map,
      position: { lat: shop.lat, lng: shop.lng },
      title: shop.name,
    });
  });
}

initMap();
